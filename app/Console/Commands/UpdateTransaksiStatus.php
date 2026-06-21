<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaksi;
use Carbon\Carbon;

class UpdateTransaksiStatus extends Command
{
    protected $signature = 'transaksi:update-status';
    protected $description = 'Update transaksi menjadi unpaid jika lewat jatuh tempo';

    public function handle()
{
    $today = now()->startOfDay();

    // Ubah unpaid jadi overdue kalau lewat jatuh tempo
    $overdue = Transaksi::where('status', 'unpaid')
        ->whereDate('jatuh_tempo', '<', $today)
        ->update([
            'status' => 'overdue'
        ]);

    // Ubah overdue balik jadi unpaid kalau belum lewat
    $unpaid = Transaksi::where('status', 'overdue')
        ->whereDate('jatuh_tempo', '>=', $today)
        ->update([
            'status' => 'unpaid'
        ]);

    $this->info("Overdue updated: {$overdue}");
    $this->info("Unpaid updated: {$unpaid}");
}



    

}
