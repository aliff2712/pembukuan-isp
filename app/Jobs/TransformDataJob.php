<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\MikhmonImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransformDataJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(
        public string $batchId,
        public ?int $importLogId = null,
    ) {}

    /**
     * FIX: Hapus AggregateDailyJob::dispatch() di akhir handle().
     *      Pipeline sudah diatur oleh Bus::chain di controller.
     *      Dispatch manual menyebabkan AggregateDailyJob jalan dua kali.
     */
    public function handle(): void
    {
        Log::info('TransformDataJob: Starting', [
            'batch_id'  => $this->batchId,
            'import_id' => $this->importLogId,
        ]);

        try {
            $rawCount = DB::table('raw_mikhmon_imports')
                ->where('import_batch_id', $this->batchId)
                ->leftJoin(
                    'mikhmon_sales_staging',
                    'raw_mikhmon_imports.id',
                    '=',
                    'mikhmon_sales_staging.raw_id'
                )
                ->whereNull('mikhmon_sales_staging.raw_id')
                ->count();

            if ($rawCount === 0) {
                Log::warning('TransformDataJob: No unstaged records found', [
                    'batch_id' => $this->batchId,
                ]);

                if ($this->importLogId) {
                    MikhmonImportLog::find($this->importLogId)?->update([
                        'status' => 'processing',
                        'log'    => "⚠️ Tahap 2: Tidak ada data baru untuk diproses.\n"
                                 . "   Melanjutkan ke tahap berikutnya...",
                    ]);
                }

                // ✅ Tidak dispatch manual — Bus::chain yang lanjutkan
                return;
            }

            DB::transaction(function () {
                DB::insert(
                    'INSERT INTO mikhmon_sales_staging
                        (raw_id, sale_datetime, username, profile, price, batch_id, created_at, updated_at)
                    SELECT
                        raw_mikhmon_imports.id,
                        STR_TO_DATE(
                            CONCAT(raw_mikhmon_imports.date_raw, \' \', raw_mikhmon_imports.time_raw),
                            \'%b/%d/%Y %H:%i:%s\'
                        ) AS sale_datetime,
                        raw_mikhmon_imports.username,
                        raw_mikhmon_imports.profile,
                        CAST(
                            REPLACE(
                                REPLACE(
                                    REPLACE(raw_mikhmon_imports.price_raw, \'Rp\', \'\'),
                                    \'.\', \'\'
                                ),
                                \',\', \'.\'
                            ) AS DECIMAL(12, 2)
                        ) AS price,
                        raw_mikhmon_imports.import_batch_id AS batch_id,
                        NOW() AS created_at,
                        NOW() AS updated_at
                    FROM raw_mikhmon_imports
                    LEFT JOIN mikhmon_sales_staging
                        ON raw_mikhmon_imports.id = mikhmon_sales_staging.raw_id
                    WHERE raw_mikhmon_imports.import_batch_id = ?
                        AND mikhmon_sales_staging.raw_id IS NULL
                        AND raw_mikhmon_imports.price_raw IS NOT NULL
                        AND CAST(
                            REPLACE(
                                REPLACE(
                                    REPLACE(raw_mikhmon_imports.price_raw, \'Rp\', \'\'),
                                    \'.\', \'\'
                                ),
                                \',\', \'.\'
                            ) AS DECIMAL(12, 2)
                        ) > 0
                    ',
                    [$this->batchId]
                );

                Log::info('TransformDataJob: SQL insert completed', [
                    'batch_id' => $this->batchId,
                ]);
            });

            if ($this->importLogId) {
                MikhmonImportLog::find($this->importLogId)?->update([
                    'status' => 'processing',
                    'log'    => "✅ Tahap 2: Memproses data selesai.\n"
                             . "   Melanjutkan ke tahap berikutnya...",
                ]);
            }

            Log::info('TransformDataJob: Completed successfully', [
                'batch_id'  => $this->batchId,
                'import_id' => $this->importLogId,
            ]);

            // ✅ Tidak dispatch AggregateDailyJob::dispatch() di sini
            // Bus::chain di controller yang mengatur urutan selanjutnya

        } catch (\Exception $e) {
            Log::error('TransformDataJob: Failed', [
                'batch_id'  => $this->batchId,
                'error'     => $e->getMessage(),
                'import_id' => $this->importLogId,
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('TransformDataJob: FAILED', [
            'batch_id'  => $this->batchId,
            'error'     => $exception->getMessage(),
            'import_id' => $this->importLogId,
        ]);

        if ($this->importLogId) {
            MikhmonImportLog::find($this->importLogId)?->update([
                'status' => 'failed',
                'log'    => "❌ Tahap 2 gagal: Terjadi kesalahan saat memproses data.\n"
                         . "   Detail: {$exception->getMessage()}\n"
                         . "   Silakan hubungi admin atau coba lagi.",
            ]);
        }
    }
}