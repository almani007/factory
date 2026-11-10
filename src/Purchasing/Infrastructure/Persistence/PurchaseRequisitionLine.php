<?php

namespace Purchasing\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequisitionLine extends Model
{
    protected $table = 'purchase_requisition_lines';

    protected $fillable = [
        'purchase_requisition_id', 'tenant_id', 'item_id', 'quantity',
        'unit_id', 'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }
}