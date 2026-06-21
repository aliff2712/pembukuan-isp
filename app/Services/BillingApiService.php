<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillingApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.billing.url');
        $this->apiKey  = config('services.billing.key');
    }

    public function getTransaksiLunas(string $bulan): array
    {
        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->withOptions(['verify' => true])
                ->timeout(30)
                ->retry(2, 500)
                ->get("{$this->baseUrl}/api/transaksi-lunas", [
                    'api_key' => $this->apiKey,
                    'bulan'   => $bulan,
                ]);

            if (!$response->ok()) {
                Log::warning('BillingApi: transaksi error', [
                    'status' => $response->status(),
                    'bulan'  => $bulan,
                ]);
                return ['success' => false, 'data' => []];
            }

            $body = $response->json();

            if (!isset($body['success'], $body['data']) || !is_array($body['data'])) {
                Log::warning('BillingApi: struktur response transaksi tidak valid', ['body' => $body]);
                return ['success' => false, 'data' => []];
            }

            return $body;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('BillingApi: koneksi transaksi gagal', ['message' => $e->getMessage()]);
            return ['success' => false, 'data' => [], 'error' => 'connection'];
        } catch (\Exception $e) {
            Log::error('BillingApi: exception transaksi', ['message' => $e->getMessage()]);
            return ['success' => false, 'data' => [], 'error' => 'exception'];
        }
    }

    public function getPelanggan(): array
    {
        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->withOptions(['verify' => true])
                ->timeout(30)
                ->retry(2, 500)
                ->get("{$this->baseUrl}/api/pelanggan", [
                    'api_key' => $this->apiKey,
                ]);

            if (!$response->ok()) {
                Log::warning('BillingApi: pelanggan error', [
                    'status' => $response->status(),
                ]);
                return ['success' => false, 'data' => []];
            }

            $body = $response->json();

            if (!isset($body['success'], $body['data']) || !is_array($body['data'])) {
                Log::warning('BillingApi: struktur response pelanggan tidak valid', ['body' => $body]);
                return ['success' => false, 'data' => []];
            }

            return $body;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('BillingApi: koneksi pelanggan gagal', ['message' => $e->getMessage()]);
            return ['success' => false, 'data' => [], 'error' => 'connection'];
        } catch (\Exception $e) {
            Log::error('BillingApi: exception pelanggan', ['message' => $e->getMessage()]);
            return ['success' => false, 'data' => [], 'error' => 'exception'];
        }
    }
}
