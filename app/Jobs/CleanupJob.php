<?php

namespace App\Jobs;

use App\Models\MikhmonImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupJob implements ShouldQueue
{
    use Queueable;

    /**
     * Timeout for job (5 minutes)
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param string $batchId Batch ID to clean
     * @param int|null $importLogId ID of import log record
     * @param bool $deleteRawData Whether to also delete raw_mikhmon_imports
     */
    public function __construct(
        public string $batchId,
        public ?int $importLogId = null,
        public bool $deleteRawData = true,
    ) {}

    /**
     * Execute the job.
     *
     * Strategy:
     * - Delete staging data (mikhmon_sales_staging) by batch_id
     * - Optionally delete raw data (raw_mikhmon_imports)
     * - Keep journal entries (they are the final processed data)
     * - Use batch delete to avoid memory issues
     * - Log cleanup activity for audit trail
     *
     * Performance:
     * - Single DELETE query filtered by batch_id
     * - Index on batch_id makes deletion fast O(log n)
     * - Memory: O(1) - no data loaded into memory
     *
     * Important:
     * - DO NOT delete journal_entries (final output)
     * - DO NOT delete daily_voucher_sales (final output)
     * - Only clean staging/raw tables
     */
    public function handle(): void
    {
        Log::info('CleanupJob: Starting', [
            'batch_id'        => $this->batchId,
            'import_id'       => $this->importLogId,
            'delete_raw_data' => $this->deleteRawData,
        ]);

        try {
            DB::transaction(function () {
                // Step 1: Delete staging data
                $stagingDeleted = DB::table('mikhmon_sales_staging')
                    ->where('batch_id', $this->batchId)
                    ->delete();

                Log::info('CleanupJob: Staging data deleted', [
                    'batch_id' => $this->batchId,
                    'deleted'  => $stagingDeleted,
                ]);

                // Step 2: Delete raw data (optional)
                if ($this->deleteRawData) {
                    $rawDeleted = DB::table('raw_mikhmon_imports')
                        ->where('import_batch_id', $this->batchId)
                        ->delete();

                    Log::info('CleanupJob: Raw data deleted', [
                        'batch_id' => $this->batchId,
                        'deleted'  => $rawDeleted,
                    ]);
                }
            });

            Log::info('CleanupJob: Completed successfully', [
                'batch_id'  => $this->batchId,
                'import_id' => $this->importLogId,
            ]);

            // Update import log to mark as done
            if ($this->importLogId) {
                MikhmonImportLog::find($this->importLogId)?->update([
                    'status' => 'done',
                    'log'    => '✅ Import sukses. Semua proses selesai.',
                ]);
            }

        } catch (\Exception $e) {
            Log::error('CleanupJob: Failed', [
                'batch_id'   => $this->batchId,
                'error'      => $e->getMessage(),
                'import_id'  => $this->importLogId,
            ]);
            throw $e;
        }
    }
}
