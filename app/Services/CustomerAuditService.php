<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class CustomerAuditService
{
    public function log(string $entityType, int $entityId, string $action, ?array $oldValue, ?array $newValue, ?string $description = null, ?int $userId = null, ?array $metadata = null): void
    {
        AuditLog::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $userId,
            'action' => $action,
            'old_value' => $oldValue ? json_encode($oldValue) : null,
            'new_value' => $newValue ? json_encode($newValue) : null,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        Log::info('CustomerAuditService: logged', [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'user_id' => $userId,
        ]);
    }
}
