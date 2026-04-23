<?php

namespace App\Services;

use App\Enums\JournalSourceType;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\SinkronTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SinkronJournalizeService
 *
 * Menjurnalkan SinkronTransaksi (transaksi lunas dari billing API)
 * ke dalam journal_entries & journal_lines.
 *
 * Pola akuntansi per transaksi lunas:
 *   DEBIT   1101 - Kas                        (jumlah)
 *   CREDIT  4102 - Pendapatan Langganan Member (jumlah)
 */
class SinkronJournalizeService
{
    private const COA_KAS           = '1101';
    private const COA_PENDAPATAN    = '4102';
    private const SOURCE_TYPE       = 'sinkron_billing';

    private int $created  = 0;
    private int $skipped  = 0;
    private array $errors = [];

    // =========================================================
    // MAIN: Jurnalkan satu record SinkronTransaksi
    // =========================================================

    public function journalize(SinkronTransaksi $trx): bool
    {
        // 1. Guard: sudah dijurnal sebelumnya?
        $alreadyExists = JournalEntry::where('source_type', self::SOURCE_TYPE)
            ->where('source_id', $trx->id)
            ->exists();

        if ($alreadyExists) {
            $this->skipped++;
            return false;
        }

        // 2. Validasi jumlah
        if ((float) $trx->jumlah <= 0) {
            Log::warning('SinkronJournalize: jumlah tidak valid', [
                'id'     => $trx->id,
                'jumlah' => $trx->jumlah,
            ]);
            $this->skipped++;
            return false;
        }

        // 3. Ambil COA (fail fast jika belum ada di DB)
        $coaKas        = ChartOfAccount::where('account_code', self::COA_KAS)->first();
        $coaPendapatan = ChartOfAccount::where('account_code', self::COA_PENDAPATAN)->first();

        if (!$coaKas || !$coaPendapatan) {
            $missing = collect([self::COA_KAS, self::COA_PENDAPATAN])
                ->filter(fn($code) => !ChartOfAccount::where('account_code', $code)->exists())
                ->implode(', ');

            Log::error('SinkronJournalize: COA tidak ditemukan', ['missing' => $missing]);
            $this->errors[] = "COA {$missing} belum ada. Jalankan seeder terlebih dahulu.";
            return false;
        }

        // 4. Buat jurnal dalam transaction DB
        try {
            DB::transaction(function () use ($trx, $coaKas, $coaPendapatan) {
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
                    'total_debit'   => $trx->jumlah,
                    'total_credit'  => $trx->jumlah,
                ]);

                // Debit: Kas
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $coaKas->id,
                    'debit'            => $trx->jumlah,
                    'credit'           => 0,
                ]);

                // Credit: Pendapatan Langganan Member
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $coaPendapatan->id,
                    'debit'            => 0,
                    'credit'           => $trx->jumlah,
                ]);
            });

            $this->created++;
            return true;

        } catch (\Throwable $e) {
            Log::error('SinkronJournalize: gagal buat jurnal', [
                'trx_id' => $trx->id,
                'error'  => $e->getMessage(),
            ]);
            $this->errors[] = "Gagal jurnal ID {$trx->id}: " . $e->getMessage();
            return false;
        }
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
