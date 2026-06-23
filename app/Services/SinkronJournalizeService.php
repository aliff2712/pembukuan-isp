<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\SinkronTransaksi;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SinkronJournalizeService
 *
 * Menjurnalkan SinkronTransaksi (transaksi lunas dari billing API)
 * ke dalam journal_entries & journal_lines.
 *
 * Pola akuntansi per transaksi lunas:
 *   DEBIT   1101 - Kas
 *   CREDIT  4102 - Pendapatan Langganan Member
 *
 * PENTING: journalize() mengembalikan string status, bukan boolean.
 * Caller WAJIB menangani setiap kemungkinan nilai balik:
 *   - 'created'            : jurnal baru berhasil dibuat
 *   - 'already_journalized': sudah ada jurnal sebelumnya (aman, bukan error)
 *   - 'invalid_amount'      : jumlah <= 0, data tidak valid
 *   - 'coa_missing'         : akun COA belum di-seed
 *   - 'failed'              : gagal karena sebab lain (lihat log/errors())
 */
class SinkronJournalizeService
{
    private const COA_KAS        = '1101';
    private const COA_PENDAPATAN = '4102';
    private const SOURCE_TYPE    = 'sinkron_billing';

    private int $created  = 0;
    private int $skipped  = 0;
    private array $errors = [];

    private ?ChartOfAccount $coaKas        = null;
    private ?ChartOfAccount $coaPendapatan = null;
    private bool $coaLoaded = false;

    // =========================================================
    // COA loader — dipanggil sekali per instance, bukan per transaksi
    // =========================================================

    private function loadCoa(): void
    {
        if ($this->coaLoaded) {
            return;
        }

        $this->coaKas        = ChartOfAccount::where('account_code', self::COA_KAS)->first();
        $this->coaPendapatan = ChartOfAccount::where('account_code', self::COA_PENDAPATAN)->first();
        $this->coaLoaded     = true;
    }

    // =========================================================
    // MAIN: Jurnalkan satu record SinkronTransaksi
    // =========================================================

    public function journalize(SinkronTransaksi $trx): string
    {
        $alreadyExists = JournalEntry::where('source_type', self::SOURCE_TYPE)
            ->where('source_id', $trx->id)
            ->exists();

        if ($alreadyExists) {
            $this->skipped++;
            return 'already_journalized';
        }

        if ((float) $trx->jumlah <= 0) {
            Log::warning('SinkronJournalize: jumlah tidak valid', [
                'id'     => $trx->id,
                'jumlah' => $trx->jumlah,
            ]);
            $this->skipped++;
            return 'invalid_amount';
        }

        $this->loadCoa();

        if (!$this->coaKas || !$this->coaPendapatan) {
            $missing = collect([self::COA_KAS, self::COA_PENDAPATAN])
                ->filter(fn($code) => !ChartOfAccount::where('account_code', $code)->exists())
                ->implode(', ');

            Log::error('SinkronJournalize: COA tidak ditemukan', ['missing' => $missing]);
            $this->errors[] = "COA {$missing} belum ada. Jalankan seeder terlebih dahulu.";
            return 'coa_missing';
        }

        try {
            DB::transaction(function () use ($trx) {
                $tanggal = $trx->tanggal_bayar
                    ? \Carbon\Carbon::parse($trx->tanggal_bayar)->toDateString()
                    : now()->toDateString();

                $entry = JournalEntry::create([
                    'journal_date'  => $tanggal,
                    'description'   => 'Tagihan billing: ' . $trx->nama_pelanggan
                                     . ($trx->bulan_tagihan ? ' (' . $trx->bulan_tagihan . ')' : ''),
                    'source_type'   => self::SOURCE_TYPE,
                    'source_id'     => $trx->id,
                    'reference_no'  => $trx->kode_transaksi,
                    'total_debit'   => 0,
                    'total_credit'  => 0,
                ]);

                // Debit: Kas
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $this->coaKas->id,
                    'debit'            => $trx->jumlah,
                    'credit'           => 0,
                ]);

                // Credit: Pendapatan Langganan Member
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $this->coaPendapatan->id,
                    'debit'            => 0,
                    'credit'           => $trx->jumlah,
                ]);

                // Hitung total dari SUM baris aktual, bukan asumsi manual,
                // supaya tetap konsisten kalau suatu saat jurnal punya >2 baris
                $entry->update([
                    'total_debit'  => $entry->lines()->sum('debit'),
                    'total_credit' => $entry->lines()->sum('credit'),
                ]);

                $trx->update([
                    'is_journalized' => true,
                    'journalized_at' => now(),
                ]);
            });

            $this->created++;
            return 'created';

        } catch (QueryException $e) {
            // Duplicate key dari unique constraint = race condition,
            // ada proses lain yang sudah duluan membuat jurnal ini
            if ($this->isDuplicateKeyError($e)) {
                Log::info('SinkronJournalize: race condition terdeteksi, jurnal sudah dibuat proses lain', [
                    'trx_id' => $trx->id,
                ]);
                $this->skipped++;
                return 'already_journalized';
            }

            Log::error('SinkronJournalize: gagal buat jurnal (query error)', [
                'trx_id' => $trx->id,
                'error'  => $e->getMessage(),
            ]);
            $this->errors[] = "Gagal jurnal ID {$trx->id}: " . $e->getMessage();
            return 'failed';

        } catch (\Throwable $e) {
            Log::error('SinkronJournalize: gagal buat jurnal', [
                'trx_id' => $trx->id,
                'error'  => $e->getMessage(),
            ]);
            $this->errors[] = "Gagal jurnal ID {$trx->id}: " . $e->getMessage();
            return 'failed';
        }
    }

    private function isDuplicateKeyError(QueryException $e): bool
    {
        return $e->getCode() === '23000';
    }

    // =========================================================
    // BULK: Jurnalkan semua SinkronTransaksi yang belum dijurnal
    // =========================================================

    public function journalizeAll(): array
    {
        $unjournalized = SinkronTransaksi::whereNotIn('id',
            JournalEntry::where('source_type', self::SOURCE_TYPE)->pluck('source_id')
        )->get();

        foreach ($unjournalized as $trx) {
            $this->journalize($trx);
        }

        return $this->getSummary();
    }

    // =========================================================
    // BULK: Jurnalkan semua SinkronTransaksi untuk bulan tertentu
    // =========================================================

    public function journalizeByBulan(string $bulan): array
    {
        $rows = SinkronTransaksi::where('bulan_tagihan', 'like', $bulan . '%')
            ->whereNotIn('id',
                JournalEntry::where('source_type', self::SOURCE_TYPE)->pluck('source_id')
            )
            ->get();

        foreach ($rows as $trx) {
            $this->journalize($trx);
        }

        return $this->getSummary();
    }

    // =========================================================
    // RETRY: Jurnalkan ulang transaksi approved yang gagal dijurnal
    // (dipanggil dari scheduler, sebagai jaring pengaman)
    // =========================================================

    public function retryFailed(): array
    {
        $candidates = SinkronTransaksi::where('status_approval', 'approved')
            ->where('is_journalized', false)
            ->get();

        if ($candidates->isEmpty()) {
            return $this->getSummary();
        }

        Log::info('SinkronJournalize: retryFailed menemukan kandidat', [
            'count' => $candidates->count(),
            'ids'   => $candidates->pluck('id')->all(),
        ]);

        foreach ($candidates as $trx) {
            $result = $this->journalize($trx);

            if ($result === 'failed' || $result === 'coa_missing') {
                Log::error('SinkronJournalize: retryFailed gagal lagi', [
                    'trx_id' => $trx->id,
                    'result' => $result,
                ]);
            }
        }

        return $this->getSummary();
    }

    // =========================================================
    // Ringkasan hasil proses
    // =========================================================

    public function getSummary(): array
    {
        return [
            'created' => $this->created,
            'skipped' => $this->skipped,
            'errors'  => $this->errors,
        ];
    }
}