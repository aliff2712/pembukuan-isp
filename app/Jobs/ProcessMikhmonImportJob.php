<?php
namespace App\Jobs;

use App\Models\MikhmonImportLog;
use App\Services\MikhmonImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMikhmonImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 menit, tidak ada batas dari browser

    public function __construct(
        public string $filePath,
        public int $userId,
        public int $importLogId,
    ) {}

    public function handle(): void
    {
        $service = new MikhmonImportService();

        try {
            $service->importCsv($this->filePath);
            $service->transform();
            $service->aggregateDaily();
            $service->journalize();

            MikhmonImportLog::find($this->importLogId)?->update([
                'status' => 'done',
                'log'    => implode("\n", $service->log),
            ]);
        } catch (\Throwable $e) {
            MikhmonImportLog::find($this->importLogId)?->update([
                'status' => 'failed',
                'log'    => $e->getMessage(),
            ]);
        }
    }
}