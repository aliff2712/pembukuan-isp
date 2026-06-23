<?php

namespace App\Services;

use App\Models\SinkronTransaksi;
use Illuminate\Support\Facades\Log;

class PaymentImportService
{
    private const ALLOWED_METODE    = ['cash', 'transfer', 'qris', 'online'];
    private const ALLOWED_STATUS    = ['lunas'];
    private const FLAG_AMOUNT_LIMIT = 10_000_000;

    private int $totalApproved             = 0;
    private int $totalApprovedUnjournalized = 0;
    private int $totalFlagged              = 0;
    private int $totalDuplicate            = 0;
    private int $totalSkipped              = 0;

    private SinkronJournalizeService $journalizer;

    public function __construct()
    {
        // Instansiasi sekali di luar loop, bukan per transaksi
        $this->journalizer = new SinkronJournalizeService();
    }

    // =========================================================
    // MAIN
    // =========================================================

    public function process(array $transaksis): array
    {
        foreach ($transaksis as $trx) {
            $result = $this->processSingle($trx);

            match ($result) {
                'approved'               => $this->totalApproved++,
                'approved_unjournalized' => $this->totalApprovedUnjournalized++,
                'flagged'                => $this->totalFlagged++,
                'duplicate'              => $this->totalDuplicate++,
                'skipped'                => $this->totalSkipped++,
            };
        }

        return $this->getSummary();
    }

    // =========================================================
    // SINGLE PROCESS
    // =========================================================

    private function processSingle(array $trx): string
    {
        if (!$this->validate($trx)) {
            Log::warning('PaymentImport: data tidak valid, skip', ['trx' => $trx]);
            return 'skipped';
        }

        $sourceRef = (int) $trx['id_transaksi'];

        $existing = SinkronTransaksi::where('id_transaksi_billing', $sourceRef)->first();

        if ($existing) {
            Log::info('PaymentImport: duplicate di sinkron_transaksi', ['source_ref' => $sourceRef]);
            return 'duplicate';
        }

        $flagReason = $this->detectFlag($trx);

        if ($flagReason) {
            $this->createTransaksi($trx, 'flagged', $flagReason);
            return 'flagged';
        }

        // Aman -> otomatis approved & dijurnal (Straight-Through Processing)
        $newTrx = $this->createTransaksi($trx, 'approved');

        $journalResult = $this->journalizer->journalize($newTrx);

        if (!in_array($journalResult, ['created', 'already_journalized'], true)) {
            Log::error('PaymentImport: transaksi approved tapi gagal dijurnal', [
                'trx_id' => $newTrx->id,
                'result' => $journalResult,
            ]);
            return 'approved_unjournalized';
        }

        return 'approved';
    }

    // =========================================================
    // CREATE TRANSAKSI
    // =========================================================

    private function createTransaksi(array $trx, string $statusApproval, ?string $flagReason = null): SinkronTransaksi
    {
        return SinkronTransaksi::create([
            'id_transaksi_billing' => (int) $trx['id_transaksi'],
            'kode_transaksi'  => (string) substr($trx['kode_transaksi'] ?? '', 0, 50),
            'nama_pelanggan'  => (string) substr($trx['nama_pelanggan'] ?? '', 0, 150),
            'jumlah'          => (float) $trx['jumlah'],
            'tanggal_bayar'   => $trx['tanggal_bayar'],
            'area'            => $trx['area'] ?? null,
            'paket'           => $trx['paket'] ?? null,
            'metode'          => in_array($trx['metode'] ?? '', self::ALLOWED_METODE) ? $trx['metode'] : 'cash',
            'dibayar_oleh'    => $trx['dibayar_oleh'] ?? null,
            'bulan_tagihan'   => $trx['bulan_tagihan'] ?? null,
            'status'          => 'lunas',
            'status_approval' => $statusApproval,
            'flag_reason'     => $flagReason,
            'raw_data'        => $trx,
            'approved_at'     => $statusApproval === 'approved' ? now() : null,
        ]);
    }

    // =========================================================
    // DETEKSI ANOMALI BISNIS
    // =========================================================

    private function detectFlag(array $trx): ?string
    {
        if ((float) $trx['jumlah'] <= 0) {
            return 'Jumlah tidak valid: ' . $trx['jumlah'];
        }

        if ((float) $trx['jumlah'] > self::FLAG_AMOUNT_LIMIT) {
            return 'Jumlah melebihi batas wajar (> Rp ' .
                number_format(self::FLAG_AMOUNT_LIMIT, 0, ',', '.') . ')';
        }

        if (!in_array($trx['metode'] ?? '', self::ALLOWED_METODE)) {
            return 'Metode pembayaran tidak dikenali: ' . ($trx['metode'] ?? '-');
        }

        if (!in_array($trx['status'] ?? '', self::ALLOWED_STATUS)) {
            return 'Status transaksi tidak valid: ' . ($trx['status'] ?? '-');
        }

        return null;
    }

    // =========================================================
    // VALIDASI FIELD WAJIB
    // =========================================================

    private function validate(array $trx): bool
    {
        $required = ['id_transaksi', 'kode_transaksi', 'nama_pelanggan', 'jumlah', 'tanggal_bayar'];

        foreach ($required as $field) {
            if (empty($trx[$field])) {
                Log::warning("PaymentImport: field '{$field}' kosong atau tidak ada", ['trx' => $trx]);
                return false;
            }
        }

        if (!is_numeric($trx['jumlah'])) {
            Log::warning('PaymentImport: jumlah bukan angka', ['jumlah' => $trx['jumlah']]);
            return false;
        }

        if (!strtotime($trx['tanggal_bayar'])) {
            Log::warning('PaymentImport: tanggal tidak valid', ['tanggal' => $trx['tanggal_bayar']]);
            return false;
        }

        return true;
    }

    // =========================================================
    // SUMMARY
    // =========================================================

    public function getSummary(): array
    {
        return [
            'total_approved'               => $this->totalApproved,
            'total_approved_unjournalized' => $this->totalApprovedUnjournalized,
            'total_flagged'                => $this->totalFlagged,
            'total_duplicate'              => $this->totalDuplicate,
            'total_skipped'                => $this->totalSkipped,
        ];
    }
}