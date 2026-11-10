<?php

namespace Dispatch\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchLine extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'dispatch_id',
        'tenant_id',
        'item_id',
        'quantity',
        'unit_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }
}