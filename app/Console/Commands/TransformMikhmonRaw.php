<?php

namespace App\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Models\RawMikhmonImport;
use App\Models\MikhmonSalesStaging;

class TransformMikhmonRaw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
protected $signature = 'mikhmon:transform';

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
    $rawRows = RawMikhmonImport::whereDoesntHave('staging')->get();

    $batchId = now()->format('Ymd_His');
    $successCount = 0;
    $skipCount = 0;

    foreach ($rawRows as $raw) {

        // 1. Parse datetime
        try {
            $saleDatetime = Carbon::createFromFormat(
                'M/d/Y H:i:s',
                "{$raw->date_raw} {$raw->time_raw}"
            );
        } catch (\Exception $e) {
            $this->warn("ID {$raw->id}: Invalid datetime format - {$raw->date_raw} {$raw->time_raw}");
            $skipCount++;
            continue; // skip invalid datetime
        }

        // 2. Parse price
        $priceRaw = $raw->price_raw;

        if (!$priceRaw) {
            $this->warn("ID {$raw->id}: Empty price");
            $skipCount++;
            continue;
        }

        $price = str_replace(['Rp', '.', ','], ['', '', '.'], $priceRaw);

        if (!is_numeric($price) || $price <= 0) {
            $this->warn("ID {$raw->id}: Invaloid price - {$priceRaw}");
            $skipCount++;
            continue;
        }

        // 3. Insert staging
        MikhmonSalesStaging::create([
            'raw_id'        => $raw->id,
            'sale_datetime' => $saleDatetime,
            'username'      => $raw->username,
            'profile'       => $raw->profile,
            'price'         => $price,
            'batch_id'      => $batchId,
        ]);
        $successCount++;
    }

    $this->info("Transform RAW → STAGING selesai | Success: $successCount | Skipped: $skipCount");

    return Command::SUCCESS;
}

}
