<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\MikhmonImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateDailyJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(
        public string $batchId,
        public ?int $importLogId = null,
    ) {}

    /**
     * FIX #1: Hapus JournalizeJob::dispatch() di akhir handle().
     *         Pipeline sudah diatur oleh Bus::chain di controller.
     *         Dispatch manual menyebabkan JournalizeJob jalan dua kali
     *         dengan kondisi staging yang sudah tidak konsisten.
     *
     * FIX #2: ON DUPLICATE KEY UPDATE menggunakan total_transactions + VALUES()
     *         bukan replace, agar import ulang pada tanggal yang sama
     *         tidak menghapus data lama melainkan menambahkan.
     */
    public function handle(): void
    {
        Log::info('AggregateDailyJob: Starting', [
            'batch_id'  => $this->batchId,
            'import_id' => $this->importLogId,
        ]);

        try {
            $stagedCount = DB::table('mikhmon_sales_staging')
                ->where('batch_id', $this->batchId)
                ->count();

            if ($stagedCount === 0) {
                Log::warning('AggregateDailyJob: No staged records found', [
                    'batch_id' => $this->batchId,
                ]);

                if ($this->importLogId) {
                    MikhmonImportLog::find($this->importLogId)?->update([
                        'status' => 'processing',
                        'log'    => "⚠️ Tahap 3: Tidak ada data untuk dirangkum.\n"
                                 . "   Melanjutkan ke tahap berikutnya...",
                    ]);
                }

                // ✅ Tidak dispatch manual — Bus::chain yang lanjutkan ke JournalizeJob
                return;
            }

            DB::transaction(function () {
                DB::insert(
                    'INSERT INTO daily_voucher_sales
                        (sale_date, total_transactions, total_amount, created_at, updated_at)
                    SELECT
                        DATE(sale_datetime)  AS sale_date,
                        COUNT(*)             AS total_transactions,
                        SUM(price)           AS total_amount,
                        NOW()                AS created_at,
                        NOW()                AS updated_at
                    FROM mikhmon_sales_staging
                    WHERE batch_id = ?
                    GROUP BY DATE(sale_datetime)
                    ON DUPLICATE KEY UPDATE
                        total_transactions = total_transactions + VALUES(total_transactions),
                        total_amount       = total_amount + VALUES(total_amount),
                        updated_at         = NOW()
                    ',
                    [$this->batchId]
                );

                Log::info('AggregateDailyJob: SQL aggregation completed', [
                    'batch_id' => $this->batchId,
                ]);
            });

            if ($this->importLogId) {
                MikhmonImportLog::find($this->importLogId)?->update([
                    'status' => 'processing',
                    'log'    => "✅ Tahap 3: Merangkum data harian selesai.\n"
                             . "   Melanjutkan ke tahap berikutnya...",
                ]);
            }

            Log::info('AggregateDailyJob: Completed successfully', [
                'batch_id'  => $this->batchId,
                'import_id' => $this->importLogId,
            ]);

            // ✅ Tidak dispatch JournalizeJob::dispatch() di sini
            // Bus::chain di controller yang mengatur urutan selanjutnya

        } catch (\Exception $e) {
            Log::error('AggregateDailyJob: Failed', [
                'batch_id'  => $this->batchId,
                'error'     => $e->getMessage(),
                'import_id' => $this->importLogId,
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('AggregateDailyJob: FAILED', [
            'batch_id'  => $this->batchId,
            'error'     => $exception->getMessage(),
            'import_id' => $this->importLogId,
        ]);

        if ($this->importLogId) {
            MikhmonImportLog::find($this->importLogId)?->update([
                'status' => 'failed',
                'log'    => "❌ Tahap 3 gagal: Terjadi kesalahan saat merangkum data harian.\n"
                         . "   Detail: {$exception->getMessage()}\n"
                         . "   Silakan hubungi admin atau coba lagi.",
            ]);
        }
    }
}