<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyVoucherSale;
use App\Models\MikhmonSalesStaging;

class AgregateMikhmonDailySales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikhmon:aggregate-daily';

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
    $rows = MikhmonSalesStaging::selectRaw('
            DATE(sale_datetime) as sale_date,
            COUNT(*) as total_transactions,
            SUM(price) as total_amount
        ')
        ->groupByRaw('DATE(sale_datetime)')
        ->get();

    foreach ($rows as $row) {
        DailyVoucherSale::updateOrCreate(
            ['sale_date' => $row->sale_date],
            [
                'total_transactions' => $row->total_transactions,
                'total_amount'       => $row->total_amount,
            ]
        );
    }

    $this->info('Agregasi harian Mikhmon selesai');
}

}
