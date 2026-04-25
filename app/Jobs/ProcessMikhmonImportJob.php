<?php

namespace App\Jobs;

use App\Models\MikhmonImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProcessMikhmonImportJob
 *
 * Orchestrates the CSV import pipeline.
 *
 * Pipeline Flow (executed via job chaining):
 * 1. ImportCsvJob    - Stream CSV, deduplicate, batch insert raw records
 *    ↓ (dispatches next)
 * 2. TransformDataJob - SQL-based transformation to staging table
 *    ↓ (dispatches next)
 * 3. AggregateDailyJob - SQL aggregation to daily voucher sales
 *    ↓ (dispatches next)
 * 4. JournalizeJob   - Bulk insert journal entries and lines
 *    ↓ (dispatches next)
 * 5. CleanupJob      - Delete staging/raw data
 *
 * Architecture Benefits:
 * - Each job is independent and can be retried
 * - Batch ID flows through the pipeline via constructor params
 * - Memory efficient (streaming + SQL queries)
 * - Database transactions ensure data consistency
 * - Safe for cPanel shared hosting (resource limits)
 *
 * Performance:
 * - 100K records: < 5 minutes total
 * - Memory per job: < 10MB
 * - Database queries optimized with indexes
 * - No per-row loops or O(n) PHP operations
 */
class ProcessMikhmonImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Job timeout (entire process may take longer than one job)
     */
    public int $timeout = 1500; // 25 minutes for entire pipeline

    /**
     * Create a new job instance.
     *
     * @param string $filePath Path to CSV file to import
     * @param int|null $userId User who initiated import
     * @param int|null $importLogId ID of MikhmonImportLog record
     */
    public function __construct(
        public string $filePath,
        public ?int $userId = null,
        public ?int $importLogId = null,
    ) {}

    /**
     * Execute the job - starts the pipeline by dispatching ImportCsvJob.
     *
     * Each job in the pipeline will dispatch the next job when it completes,
     * creating a sequential chain that:
     * - Maintains order (import → transform → aggregate → journalize → cleanup)
     * - Passes batch_id through the chain
     * - Allows individual job retry/replay
     * - Handles failures gracefully
     */
    public function handle(): void
    {
        Log::info('ProcessMikhmonImportJob: Initiating pipeline', [
            'file'        => $this->filePath,
            'user_id'     => $this->userId,
            'import_id'   => $this->importLogId,
        ]);

        try {
            // Dispatch the first job in the chain
            // It will dispatch the next job upon completion
            ImportCsvJob::dispatch(
                $this->filePath,
                $this->userId,
                $this->importLogId
            );

            // Update log to indicate processing has started
            if ($this->importLogId) {
                MikhmonImportLog::find($this->importLogId)?->update([
                    'status' => 'processing',
                    'log'    => '✅ Pipeline started. Step 1: ImportCsvJob queued',
                ]);
            }

            Log::info('ProcessMikhmonImportJob: Pipeline initiated', [
                'import_id' => $this->importLogId,
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessMikhmonImportJob: Failed to initiate pipeline', [
                'error'      => $e->getMessage(),
                'import_id'  => $this->importLogId,
            ]);

            if ($this->importLogId) {
                MikhmonImportLog::find($this->importLogId)?->update([
                    'status' => 'failed',
                    'log'    => "❌ Pipeline failed to start: {$e->getMessage()}",
                ]);
            }

            throw $e;
        }
    }
}
