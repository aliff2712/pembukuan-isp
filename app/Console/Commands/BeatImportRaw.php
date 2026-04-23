<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\RawBeatImport;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BeatImportRaw extends Command
{
    protected $signature = 'beat:import-raw {file?}';
    protected $description = 'Import RAW Beat CSV/XLSX';

  public function handle(): int
{
    $file = $this->argument('file');

    // If no file provided, look for the latest file in a default import directory
    if (!$file) {
        $importDir = storage_path('imports/beat');
        if (!is_dir($importDir)) {
            $this->error("Import directory not found: {$importDir}");
            return Command::FAILURE;
        }

        $files = glob($importDir . '/*.{csv,xlsx,xls}', GLOB_BRACE);
        if (empty($files)) {
            $this->info("No files to import in: {$importDir}");
            return Command::SUCCESS;
        }

        // Get the most recently modified file
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $file = $files[0];
        $this->info("Using file: {$file}");
    }

    if (! file_exists($file)) {
        $this->error("File not found: {$file}");
        return Command::FAILURE;
    }

    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $batchId = now()->format('Ymd_His');

    if ($extension === 'csv') {
        $rows = array_map('str_getcsv', file($file));
    } elseif (in_array($extension, ['xlsx', 'xls'])) {
        $spreadsheet = IOFactory::load($file);
        $rows = $spreadsheet->getActiveSheet()->toArray();
    } else {
        $this->error('Unsupported file format');
        return Command::FAILURE;
    }

    foreach ($rows as $index => $row) {
        // skip header
        if ($index === 0) {
            continue;
        }

        RawBeatImport::create([
            'import_batch_id' => $batchId,
            'row_number'      => $index + 1,
            'raw_payload'     => $row,
            'imported_at'     => now(),
        ]);
    }

    $this->info('RAW Beat import completed');
    return Command::SUCCESS;
}

}
