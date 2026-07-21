<?php

namespace App\Console\Commands;

use App\Services\BillingApiService;
use App\Services\CustomerSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SyncCustomersFromBilling extends Command
{
    protected $signature = 'billing:sync-customers';
    protected $description = 'Sinkronisasi pelanggan dari API billing ke tabel sinkron_pelanggan';

    public function handle(): int
    {
        $billing = new BillingApiService();
        $service = new CustomerSyncService();

        $body = $billing->getPelanggan();

        if (!$body['success']) {
            Log::warning('billing:sync-customers failed', ['body' => $body]);
            $this->error('Gagal mengambil data pelanggan dari billing.');
            return self::FAILURE;
        }

        $result = $service->syncFromApi($body['data'] ?? [], Auth::id());

        $this->info('Sinkronisasi pelanggan selesai.');
        $this->line('Baru: ' . $result['imported']);
        $this->line('Diperbarui: ' . $result['updated']);
        $this->line('Dinonaktifkan: ' . $result['deactivated']);

        return self::SUCCESS;
    }
}
