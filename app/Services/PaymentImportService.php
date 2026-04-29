<?php

namespace App\Services;

use App\Models\PaymentStaging;
use App\Models\SinkronPelanggan;
use App\Models\SinkronTransaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * PaymentImportService
 *
 * Alur: API → Validasi → Staging
 *   - Lolos validasi → status approved → auto jurnal langsung
 *   - Gagal validasi → status flagged → tunggu review manual
 */
class PaymentImportService
{
    private const NOMINAL_MAX = 500000;
    private const NOMINAL_MIN = 1;

    private int $totalProcessed = 0;
    private int $totalApproved  = 0;
    private int $totalFlagged   = 0;
    private int $totalJournalized = 0;
    private array $flaggedReasons = [];

    // =========================================================
    // MAIN
    // =========================================================

    public function process(array $transactions): array
    {
        $this->totalProcessed   = 0;
        $this->totalApproved    = 0;
        $this->totalFlagged     = 0;
        $this->totalJournalized = 0;
        $this->flaggedReasons   = [];

        foreach ($transactions as $trx) {
            $this->processOne($trx);
        }

        // Kirim notif Telegram jika ada yang flagged
        if ($this->totalFlagged > 0) {
            try {
                (new TelegramNotificationService())->sendFlaggedAlert(
                    $this->totalFlagged,
                    $this->flaggedReasons
                );
            } catch (\Exception $e) {
                Log::error('PaymentImport: Gagal kirim notif Telegram', ['message' => $e->getMessage()]);
            }
        }

        return $this->getSummary();
    }

    // =========================================================
    // Process single transaction
    // =========================================================

    private function processOne(array $trx): void
    {
        $this->totalProcessed++;

        // STEP 1: Field wajib
        $requiredFields = ['id_transaksi', 'kode_transaksi', 'nama_pelanggan', 'jumlah', 'tanggal_bayar'];
        $missing = array_filter($requiredFields, fn($f) => !isset($trx[$f]) || $trx[$f] === '' || $trx[$f] === null);
        if (!empty($missing)) {
            $this->flagTransaction($trx, 'Field wajib tidak lengkap: ' . implode(', ', $missing));
            return;
        }

        // STEP 2: Nominal
        if (!is_numeric($trx['jumlah'])) {
            $this->flagTransaction($trx, 'Jumlah harus berupa angka');
            return;
        }
        $jumlah = (float) $trx['jumlah'];
        if ($jumlah < self::NOMINAL_MIN) {
            $this->flagTransaction($trx, 'Jumlah harus lebih dari 0');
            return;
        }
        if ($jumlah > self::NOMINAL_MAX) {
            $this->flagTransaction($trx, 'Jumlah melebihi batas maksimal (Rp ' . number_format(self::NOMINAL_MAX, 0, ',', '.') . ')');
            return;
        }

        // STEP 3: Tanggal
        try {
            $tanggal = Carbon::parse($trx['tanggal_bayar']);
        } catch (\Exception $e) {
            $this->flagTransaction($trx, 'Format tanggal tidak valid: ' . $trx['tanggal_bayar']);
            return;
        }
        if ($tanggal->isFuture()) {
            $this->flagTransaction($trx, 'Tanggal tidak boleh di masa depan');
            return;
        }

        // STEP 4: Duplikat
        $existing = PaymentStaging::where('source_ref', $trx['id_transaksi'])
            ->whereIn('status', ['pending', 'approved', 'flagged'])
            ->first();
        if ($existing) {
            $this->flagTransaction($trx, 'Data sudah ada di staging (status: ' . $existing->status . ')');
            return;
        }
        if (PaymentStaging::where('source_ref', $trx['id_transaksi'])->where('status', 'rejected')->exists()) {
            $this->flagTransaction($trx, 'Data pernah di-reject sebelumnya');
            return;
        }

        // STEP 5: Pelanggan
        if (!SinkronPelanggan::where('nama', $trx['nama_pelanggan'])->exists()) {
            $this->flagTransaction($trx, 'Pelanggan "' . $trx['nama_pelanggan'] . '" tidak ditemukan');
            return;
        }

        // STEP 6: Semua lolos → approve & langsung jurnal
        $staging = $this->approveTransaction($trx);

        if ($staging) {
            $this->journalizeStaging($staging);
        }
    }

    // =========================================================
    // Flag transaction
    // =========================================================

    private function flagTransaction(array $trx, string $reason): void
    {
        $sourceRef = $trx['id_transaksi'] ?? 'unknown';

        PaymentStaging::updateOrCreate(
            ['source_ref' => $sourceRef],
            [
                'kode_transaksi' => (string) substr($trx['kode_transaksi'] ?? '', 0, 50),
                'nama_pelanggan' => (string) substr($trx['nama_pelanggan'] ?? '', 0, 150),
                'jumlah'         => isset($trx['jumlah']) ? (float) $trx['jumlah'] : 0,
                'tanggal_bayar'  => $trx['tanggal_bayar'] ?? null,
                'area'           => isset($trx['area'])         ? (string) substr($trx['area'], 0, 100) : null,
                'paket'          => isset($trx['paket'])        ? (string) substr($trx['paket'], 0, 100) : null,
                'metode'         => isset($trx['metode'])       ? (string) substr($trx['metode'], 0, 50) : null,
                'dibayar_oleh'   => isset($trx['dibayar_oleh']) ? (string) substr($trx['dibayar_oleh'], 0, 100) : null,
                'bulan_tagihan'  => isset($trx['bulan_tagihan']) ? (string) $trx['bulan_tagihan'] : null,
                'raw_data'       => $trx,
                'status'         => 'flagged',
                'flag_reason'    => $reason,
            ]
        );

        $this->totalFlagged++;
        $this->flaggedReasons[] = [
            'source_ref' => $sourceRef,
            'nama'       => $trx['nama_pelanggan'] ?? 'N/A',
            'reason'     => $reason,
        ];

        Log::warning('PaymentImport: Flagged', ['source_ref' => $sourceRef, 'reason' => $reason]);
    }

    // =========================================================
    // Approve transaction → return PaymentStaging instance
    // =========================================================

    private function approveTransaction(array $trx): ?PaymentStaging
    {
        $sourceRef = $trx['id_transaksi'];

        $staging = PaymentStaging::updateOrCreate(
            ['source_ref' => $sourceRef],
            [
                'kode_transaksi' => (string) substr($trx['kode_transaksi'] ?? '', 0, 50),
                'nama_pelanggan' => (string) substr($trx['nama_pelanggan'] ?? '', 0, 150),
                'jumlah'         => (float) $trx['jumlah'],
                'tanggal_bayar'  => $trx['tanggal_bayar'],
                'area'           => isset($trx['area'])         ? (string) substr($trx['area'], 0, 100) : null,
                'paket'          => isset($trx['paket'])        ? (string) substr($trx['paket'], 0, 100) : null,
                'metode'         => isset($trx['metode'])       ? (string) substr($trx['metode'], 0, 50) : null,
                'dibayar_oleh'   => isset($trx['dibayar_oleh']) ? (string) substr($trx['dibayar_oleh'], 0, 100) : null,
                'bulan_tagihan'  => isset($trx['bulan_tagihan']) ? (string) $trx['bulan_tagihan'] : null,
                'raw_data'       => $trx,
                'status'         => 'approved',
                'flag_reason'    => null,
            ]
        );

        $this->totalApproved++;
        Log::info('PaymentImport: Approved', ['source_ref' => $sourceRef, 'nama' => $trx['nama_pelanggan']]);

        return $staging;
    }

    // =========================================================
    // Auto jurnal setelah approve
    // =========================================================

    private function journalizeStaging(PaymentStaging $staging): void
    {
        if ($staging->is_journalized) {
            return;
        }

        try {
            $journalizer = new SinkronJournalizeService();

            $sinkronTransaksi = SinkronTransaksi::updateOrCreate(
                ['id_transaksi_billing' => $staging->source_ref],
                [
                    'kode_transaksi' => $staging->kode_transaksi,
                    'nama_pelanggan' => $staging->nama_pelanggan,
                    'jumlah'         => $staging->jumlah,
                    'tanggal_bayar'  => $staging->tanggal_bayar,
                    'area'           => $staging->area,
                    'paket'          => $staging->paket,
                    'metode'         => $staging->metode,
                    'dibayar_oleh'   => $staging->dibayar_oleh,
                    'bulan_tagihan'  => $staging->bulan_tagihan,
                    'status'         => 'lunas',
                ]
            );

            if ($journalizer->journalize($sinkronTransaksi)) {
                $staging->markAsJournalized();
                $this->totalJournalized++;

                if ($sinkronTransaksi->shouldBeLocked()) {
                    $sinkronTransaksi->lock();
                }

                Log::info('PaymentImport: Auto-jurnal berhasil', ['source_ref' => $staging->source_ref]);
            }
        } catch (\Exception $e) {
            Log::error('PaymentImport: Gagal auto-jurnal', [
                'source_ref' => $staging->source_ref,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    // =========================================================
    // Summary
    // =========================================================

    public function getSummary(): array
    {
        return [
            'total_processed'   => $this->totalProcessed,
            'total_approved'    => $this->totalApproved,
            'total_journalized' => $this->totalJournalized,
            'total_flagged'     => $this->totalFlagged,
            'flagged_reasons'   => $this->flaggedReasons,
        ];
    }
}