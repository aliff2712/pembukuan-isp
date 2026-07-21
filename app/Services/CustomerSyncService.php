<?php

namespace App\Services;

use App\Models\SinkronPelanggan;
use App\Services\CustomerAuditService;
use Illuminate\Support\Facades\Log;

class CustomerSyncService
{
    private CustomerAuditService $auditService;

    public function __construct()
    {
        $this->auditService = new CustomerAuditService();
    }

    public function normalizeApiStatus(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));

        $inactiveValues = ['inactive', 'nonaktif', 'non active', 'disabled', 'deleted', 'hapus', 'inactive_customer'];
        $activeValues = ['active', 'aktif', 'enabled', 'active_customer'];

        if (in_array($normalized, $activeValues, true)) {
            return 'aktif';
        }

        if (in_array($normalized, $inactiveValues, true)) {
            return 'nonaktif';
        }

        return 'aktif';
    }

    public function syncFromApi(array $customers, ?int $userId = null): array
    {
        $imported = 0;
        $updated = 0;
        $deactivated = 0;
        $seenIds = [];

        foreach ($customers as $customer) {
            if (!isset($customer['id'], $customer['nama'])) {
                continue;
            }

            $id = (int) $customer['id'];
            $seenIds[] = $id;

            $status = $this->normalizeApiStatus($customer['status'] ?? null);
            $isActive = $status === 'aktif';

            $payload = [
                'nama' => (string) substr($customer['nama'] ?? '', 0, 150),
                'phone' => isset($customer['phone']) ? (string) substr($customer['phone'], 0, 20) : null,
                'paket' => isset($customer['paket']) ? (string) substr($customer['paket'], 0, 100) : null,
                'harga_paket' => (float) ($customer['harga_paket'] ?? 0),
                'area' => isset($customer['area']) ? (string) substr($customer['area'], 0, 100) : null,
                'ip_address' => !empty($customer['ip_address']) ? filter_var($customer['ip_address'], FILTER_VALIDATE_IP) ? $customer['ip_address'] : null : null,
                'diskon' => (float) ($customer['diskon'] ?? 0),
                'total_tagihan' => (float) ($customer['total_tagihan'] ?? 0),
                'tanggal_register' => $this->normalizeDate($customer['tanggal_register'] ?? null),
                'status' => $status,
                'is_active' => $isActive,
            ];

            $record = SinkronPelanggan::firstOrNew(['id_pelanggan_billing' => $id]);
            $oldValues = $record->exists ? $record->only(array_keys($payload)) : [];
            $record->fill($payload);
            $record->save();

            if ($record->wasRecentlyCreated) {
                $imported++;
            } else {
                $updated++;
            }

            if ($record->wasRecentlyCreated || $record->wasChanged()) {
                $this->auditService->log(
                    'sinkron_pelanggan',
                    $record->id,
                    $record->wasRecentlyCreated ? 'CREATE' : 'UPDATE',
                    $oldValues,
                    $record->only(array_keys($payload)),
                    'Sync pelanggan dari billing API',
                    $userId,
                    ['source' => 'billing_api', 'status' => $status]
                );

                Log::info('CustomerSyncService: customer synced', [
                    'customer_id' => $id,
                    'status' => $status,
                    'user_id' => $userId,
                ]);
            }
        }

        $missing = SinkronPelanggan::whereNotIn('id_pelanggan_billing', $seenIds)->get();

        foreach ($missing as $record) {
            if ($record->is_active) {
                $oldValues = $record->only(['status', 'is_active']);
                $record->update(['is_active' => false, 'status' => 'nonaktif']);
                $this->auditService->log(
                    'sinkron_pelanggan',
                    $record->id,
                    'DEACTIVATE',
                    $oldValues,
                    ['status' => 'nonaktif', 'is_active' => false],
                    'Pelanggan tidak muncul lagi di response API billing',
                    $userId,
                    ['source' => 'billing_api', 'reason' => 'missing_from_response']
                );
                $deactivated++;
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'deactivated' => $deactivated,
        ];
    }

    protected function normalizeDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $ts = strtotime($value);
        if ($ts === false || date('Y', $ts) <= 1000) {
            return null;
        }

        return date('Y-m-d', $ts);
    }
}
