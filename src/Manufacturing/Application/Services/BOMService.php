<?php

namespace Manufacturing\Application\Services;

use Manufacturing\Infrastructure\Persistence\BOMHeader;
use Manufacturing\Infrastructure\Persistence\BOMVersion;
use Manufacturing\Infrastructure\Persistence\BOMComponent;
use Manufacturing\Infrastructure\Persistence\Routing;
use Manufacturing\Infrastructure\Persistence\RoutingVersion;
use Manufacturing\Infrastructure\Persistence\RoutingOperation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BOMService
 *
 * Manages Bills of Materials with versioning. A production order must retain
 * the exact BOM version used at approval (BR-006).
 */
class BOMService
{
    /**
     * Create a BOM header.
     */
    public function createHeader(array $dto): BOMHeader
    {
        return BOMHeader::create([
            'tenant_id' => $dto['tenant_id'],
            'factory_id' => $dto['factory_id'] ?? null,
            'item_id' => $dto['item_id'],
            'name' => $dto['name'],
            'description' => $dto['description'] ?? null,
            'is_active' => true,
            'is_default' => (bool) ($dto['is_default'] ?? false),
        ]);
    }

    /**
     * Create a new BOM version. Effective dates control when it applies.
     */
    public function createVersion(array $dto): BOMVersion
    {
        return DB::transaction(function () use ($dto) {
            // If this version is active, deactivate other active versions for this header
            if (!empty($dto['is_active'])) {
                BOMVersion::where('bom_header_id', $dto['bom_header_id'])
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            $version = BOMVersion::create([
                'bom_header_id' => $dto['bom_header_id'],
                'tenant_id' => $dto['tenant_id'],
                'version' => $dto['version'],
                'effective_from' => $dto['effective_from'],
                'effective_to' => $dto['effective_to'] ?? null,
                'is_active' => (bool) ($dto['is_active'] ?? false),
                'standard_material_cost' => (float) ($dto['standard_material_cost'] ?? 0),
                'notes' => $dto['notes'] ?? null,
            ]);

            Log::info('BOM version created', [
                'bom_header_id' => $dto['bom_header_id'],
                'version_id' => $version->id,
                'version' => $dto['version'],
                'is_active' => (bool) ($dto['is_active'] ?? false),
            ]);

            return $version;
        });
    }

    /**
     * Add components to a BOM version.
     */
    public function addComponents(string $bomVersionId, array $components): array
    {
        $created = [];

        foreach ($components as $comp) {
            $created[] = BOMComponent::create([
                'bom_version_id' => $bomVersionId,
                'tenant_id' => $comp['tenant_id'],
                'item_id' => $comp['item_id'],
                'quantity' => (float) $comp['quantity'],
                'unit_id' => $comp['unit_id'] ?? null,
                'expected_wastage_percentage' => (float) ($comp['expected_wastage_percentage'] ?? 0),
                'is_alternative' => (bool) ($comp['is_alternative'] ?? false),
                'notes' => $comp['notes'] ?? null,
            ]);
        }

        return $created;
    }

    /**
     * Get the active BOM version for an item (by effective date).
     */
    public function getActiveVersion(string $bomHeaderId, ?string $effectiveDate = null): ?BOMVersion
    {
        $query = BOMVersion::where('bom_header_id', $bomHeaderId)
            ->where('is_active', true);

        if ($effectiveDate) {
            $query->where('effective_from', '<=', $effectiveDate)
                ->where(function ($q) use ($effectiveDate) {
                    $q->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $effectiveDate);
                });
        }

        return $query->first();
    }

    /**
     * Get all components for a BOM version.
     */
    public function getComponents(string $bomVersionId): array
    {
        return BOMComponent::where('bom_version_id', $bomVersionId)
            ->with('item')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'item_id' => $c->item_id,
                    'item_name' => optional($c->item)->name,
                    'item_sku' => optional($c->item)->sku,
                    'quantity' => (float) $c->quantity,
                    'unit_id' => $c->unit_id,
                    'wastage_percentage' => (float) $c->expected_wastage_percentage,
                    'is_alternative' => (bool) $c->is_alternative,
                    'notes' => $c->notes,
                ];
            })
            ->toArray();
    }

    /**
     * Create a routing (manufacturing process definition).
     */
    public function createRouting(array $dto): Routing
    {
        return Routing::create([
            'tenant_id' => $dto['tenant_id'],
            'factory_id' => $dto['factory_id'] ?? null,
            'item_id' => $dto['item_id'],
            'name' => $dto['name'],
            'description' => $dto['description'] ?? null,
            'is_active' => true,
            'is_default' => (bool) ($dto['is_default'] ?? false),
        ]);
    }

    /**
     * Create a routing version with operations.
     */
    public function createRoutingVersion(array $dto): RoutingVersion
    {
        return DB::transaction(function () use ($dto) {
            if (!empty($dto['is_active'])) {
                RoutingVersion::where('routing_id', $dto['routing_id'])
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            $version = RoutingVersion::create([
                'routing_id' => $dto['routing_id'],
                'tenant_id' => $dto['tenant_id'],
                'version' => $dto['version'],
                'effective_from' => $dto['effective_from'],
                'effective_to' => $dto['effective_to'] ?? null,
                'is_active' => (bool) ($dto['is_active'] ?? false),
                'notes' => $dto['notes'] ?? null,
            ]);

            // Create routing operations
            if (!empty($dto['operations'])) {
                foreach ($dto['operations'] as $op) {
                    RoutingOperation::create([
                        'routing_version_id' => $version->id,
                        'tenant_id' => $dto['tenant_id'],
                        'sequence' => (int) ($op['sequence'] ?? 0),
                        'operation_name' => $op['operation_name'],
                        'work_centre_id' => $op['work_centre_id'] ?? null,
                        'machine_id' => $op['machine_id'] ?? null,
                        'setup_time_minutes' => (int) ($op['setup_time_minutes'] ?? 0),
                        'run_time_per_unit_minutes' => (float) ($op['run_time_per_unit_minutes'] ?? 0),
                        'is_active' => true,
                    ]);
                }
            }

            return $version;
        });
    }
}