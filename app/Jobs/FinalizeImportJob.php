<?php

namespace App\Jobs;

use App\Models\MikhmonImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FinalizeImportJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public function __construct(
        public string $batchId,
        public int $importLogId,
    ) {}

    public function handle(): void
    {
        Log::info('FinalizeImportJob: start', [
            'batch_id'      => $this->batchId,
            'import_log_id' => $this->importLogId,
        ]);

        try {
            $log = MikhmonImportLog::find($this->importLogId);

            if (!$log) {
                Log::warning('FinalizeImportJob: log not found', [
                    'import_log_id' => $this->importLogId,
                ]);
                return;
            }

            $log->update([
                'status' => 'done',
                'log'    => $this->appendLog(
                    $log->log,
                    "✅ Semua tahap selesai. Data berhasil diimpor."
                ),
            ]);

            Log::info('FinalizeImportJob: success', [
                'batch_id'      => $this->batchId,
                'import_log_id' => $this->importLogId,
            ]);

        } catch (\Throwable $e) {
            Log::error('FinalizeImportJob: failed', [
                'batch_id'      => $this->batchId,
                'import_log_id' => $this->importLogId,
                'error'         => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('FinalizeImportJob: FAILED', [
            'batch_id'      => $this->batchId,
            'import_log_id' => $this->importLogId,
            'error'         => $exception->getMessage(),
        ]);

        MikhmonImportLog::find($this->importLogId)?->update([
            'status' => 'failed',
            'log'    => "❌ Proses import tidak dapat diselesaikan.\n"
                      . "   Detail: {$exception->getMessage()}\n"
                      . "   Silakan hubungi admin atau coba lagi.",
        ]);
    }

    private function appendLog(?string $existingLog, string $newLine): string
    {
        return trim(($existingLog ?? '') . "\n" . $newLine);
    }
}