<?php

namespace Dispatch\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchLine extends Model
{
    protected $table = 'dispatch_lines';

    protected $fillable = [
        'dispatch_id', 'tenant_id', 'item_id', 'quantity', 'unit_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\Products\Infrastructure\Persistence\Item::class);
    }
}