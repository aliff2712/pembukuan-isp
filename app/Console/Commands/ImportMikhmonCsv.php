<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RawMikhmonImport;

class ImportMikhmonCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   protected $signature = 'mikhmon:import {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
{
    $path = $this->argument('path');

    if (!file_exists($path)) {
        $this->error("File not found: {$path}");
        return Command::FAILURE;
    }

    $batchId = now()->format('Ymd_His');

    $file = fopen($path, 'r');

    while (($row = fgetcsv($file)) !== false) {

        // 1. skip baris kosong
        if (count($row) < 2) continue;

        // 2. skip meta
        if (str_contains($row[0], 'Selling Report')) continue;
        if (str_contains($row[0], 'Total')) continue;

        // 3. skip header
        if (!is_numeric($row[0])) continue;

        // 4. simpan RAW
        RawMikhmonImport::create([
            'import_batch_id' => $batchId,
            'row_number'      => (int) $row[0],
            'date_raw'        => $row[1],
            'time_raw'        => $row[2],
            'username'        => $row[3],
            'profile'         => $row[4],
            'comment'         => $row[5] ?? null,
            'price_raw'       => $row[6] ?? null,
            'raw_payload'     => json_encode($row),
            'imported_at'     => now(),
        ]);
    }

    fclose($file);

    $this->info('Import selesai. Batch: ' . $batchId);

    return Command::SUCCESS;
}

}
