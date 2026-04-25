<?php

namespace App\Jobs;

use App\Models\RawMikhmonImport;
use App\Models\MikhmonImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportCsvJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    private const BATCH_SIZE = 500;

    public function __construct(
        public string $filePath,
        public string $batchId,
        public int $importLogId,
    ) {}

    public function handle(): void
    {
        Log::info('ImportCsvJob: start', [
            'batch_id'  => $this->batchId,
            'import_id' => $this->importLogId,
        ]);

        if (!file_exists($this->filePath)) {
            throw new \RuntimeException("File not found: {$this->filePath}");
        }

        $insertCount = 0;
        $skipCount   = 0;
        $batch       = [];

        $file = fopen($this->filePath, 'r');

        // Handle BOM
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        try {
            while (($row = fgetcsv($file)) !== false) {

                if (count($row) < 2) continue;

                $col0 = trim($row[0]);

                if (
                    str_contains($col0, 'Selling Report') ||
                    str_contains($col0, 'Total') ||
                    !is_numeric($col0)
                ) {
                    continue;
                }

                $contentHash = md5(implode('|', [
                    trim($row[1] ?? ''),
                    trim($row[2] ?? ''),
                    trim($row[3] ?? ''),
                    trim($row[4] ?? ''),
                    trim($row[6] ?? ''),
                ]));

                if (RawMikhmonImport::where('content_hash', $contentHash)->exists()) {
                    $skipCount++;
                    continue;
                }

                $batch[] = [
                    'import_batch_id' => $this->batchId,
                    'row_number'      => (int) $col0,
                    'date_raw'        => trim($row[1]),
                    'time_raw'        => trim($row[2]),
                    'username'        => trim($row[3]),
                    'profile'         => trim($row[4]),
                    'comment'         => $row[5] ?? null,
                    'price_raw'       => $row[6] ?? null,
                    'raw_payload'     => json_encode($row),
                    'content_hash'    => $contentHash,
                    'imported_at'     => now(),
                ];

                $insertCount++;

                if (count($batch) >= self::BATCH_SIZE) {
                    RawMikhmonImport::insert($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                RawMikhmonImport::insert($batch);
            }

        } finally {
            fclose($file);
        }

        Log::info('ImportCsvJob: success', [
            'batch_id' => $this->batchId,
            'inserted' => $insertCount,
            'skipped'  => $skipCount,
        ]);

        MikhmonImportLog::where('id', $this->importLogId)->update([
            'status' => 'processing',
            'log'    => "✅ Tahap 1: Membaca file CSV selesai.\n"
                      . "   Data dibaca: {$insertCount}, Dilewati (duplikat): {$skipCount}\n"
                      . "   Melanjutkan ke tahap berikutnya...",
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ImportCsvJob: failed', [
            'error'    => $e->getMessage(),
            'batch_id' => $this->batchId,
        ]);

        MikhmonImportLog::where('id', $this->importLogId)->update([
            'status' => 'failed',
            'log'    => "❌ Tahap 1 gagal: Terjadi kesalahan saat membaca file CSV.\n"
                      . "   Detail: {$e->getMessage()}\n"
                      . "   Silakan hubungi admin atau coba lagi.",
        ]);
    }
}