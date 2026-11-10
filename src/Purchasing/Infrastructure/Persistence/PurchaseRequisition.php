<?php

namespace Purchasing\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PurchaseRequisition - internal request to purchase materials.
 */
class PurchaseRequisition extends Model
{
    protected $table = 'purchase_requisitions';

    protected $fillable = [
        'tenant_id', 'factory_id', 'requisition_number', 'requested_by',
        'required_date', 'status', 'notes',
    ];

    protected $casts = [
        'required_date' => 'date',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionLine::class);
    }
}