<?php

namespace Inventory\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'warehouse_id',
        'movement_type',
        'reference',
        'source_document_type',
        'source_document_id',
        'source_document_line',
        'transaction_date',
        'posting_date',
        'unit_cost',
        'total_cost',
        'user_id',
        'reason',
        'reversal_reference_id',
        'posted_at',
        'posted_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'transaction_date' => 'datetime',
        'posting_date' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(StockMovementLine::class, 'movement_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Organisation\Models\Warehouse::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\Products\Models\Item::class, 'item_id');
    }

    public function reversal(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'reversal_reference_id');
    }
}