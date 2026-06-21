<?php

namespace App\Services;

use App\Models\SinkronTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentApprovalService
{
    // =========================================================
    // APPROVE SINGLE
    // =========================================================

    public function approve(SinkronTransaksi $trx, int $userId): bool
    {
        // Guard: sudah final (approved/rejected)
        if (in_array($trx->status_approval, ['approved', 'rejected'])) {
            throw new \Exception('Data sudah final, tidak bisa diubah.');
        }

        // Guard: terkunci
        if ($trx->is_locked) {
            throw new \Exception('Data sudah terkunci.');
        }

        // Idempotent: sudah approved & dijurnal → anggap sukses
        if ($trx->status_approval === 'approved' && $trx->is_journalized) {
            return true;
        }

        return DB::transaction(function () use ($trx, $userId) {

            if ((float) $trx->jumlah <= 0) {
                throw new \Exception('Jumlah tidak valid.');
            }

            // 1. Update status
            $trx->update([
                'status_approval' => 'approved',
                'approved_by'     => $userId,
                'approved_at'     => now(),
                'flag_reason'     => null,
            ]);

            // 2. Jurnal
            $journalizer = new SinkronJournalizeService();
            $ok = $journalizer->journalize($trx);

            if (!$ok && !$trx->is_journalized) {
                throw new \Exception('Gagal membuat jurnal.');
            }

            Log::info('PaymentApproval: success', [
                'trx_id'     => $trx->id,
                'source_ref' => $trx->id_transaksi_billing,
                'user_id'    => $userId,
            ]);

            return true;
        });
    }

    // =========================================================
    // BULK APPROVE
    // =========================================================

    public function bulkApprove(array $ids, int $userId): array
    {
        $success = 0;
        $failed  = 0;
        $skipped = 0;

        // Hanya ambil yang benar-benar actionable di level DB
        $rows = SinkronTransaksi::whereIn('id', $ids)
            ->whereNotIn('status_approval', ['approved', 'rejected'])
            ->where('is_locked', false)
            ->get();

        $skipped = count($ids) - $rows->count();

        foreach ($rows as $trx) {
            try {
                $this->approve($trx, $userId);
                $success++;
            } catch (\Throwable $e) {
                Log::warning('PaymentApproval: bulk approve gagal', [
                    'trx_id'     => $trx->id,
                    'error'      => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        return compact('success', 'failed', 'skipped');
    }
}