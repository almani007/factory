<?php

namespace Shared\Support;

use Illuminate\Support\Facades\DB;

/**
 * Transactional outbox.
 *
 * Events are written in the same transaction as the business change and are
 * published later by the outbox dispatcher. This prevents a committed posting
 * from losing its integration side effects.
 */
class Outbox
{
    public static function record(
        string $eventType,
        string $aggregateType,
        int $aggregateId,
        array $payload = [],
        ?int $tenantId = null
    ): int {
        return DB::table('outbox_events')->insertGetId([
            'tenant_id' => $tenantId ?? TenantContext::currentTenantId(),
            'event_type' => $eventType,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'payload' => json_encode($payload),
            'status' => 'pending',
            'available_at' => now(),
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
