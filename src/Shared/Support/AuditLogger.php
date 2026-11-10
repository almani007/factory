<?php

namespace Shared\Support;

use Illuminate\Support\Facades\DB;

/**
 * Writes security and business audit records.
 *
 * Called inside the same database transaction as the change it describes, so
 * an audit entry can never exist for a rolled-back posting.
 */
class AuditLogger
{
    public static function record(
        string $action,
        string $subjectType,
        ?int $subjectId = null,
        array $oldValues = [],
        array $newValues = [],
        ?int $tenantId = null,
        ?int $userId = null
    ): void {
        DB::table('audit_logs')->insert([
            'tenant_id' => $tenantId ?? TenantContext::currentTenantId(),
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'old_values' => $oldValues === [] ? null : json_encode($oldValues),
            'new_values' => $newValues === [] ? null : json_encode($newValues),
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }
}
