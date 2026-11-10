<?php

namespace App\Modules\Production\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use App\Modules\Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionEntry extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'production_order_id',
        'work_order_id',
        'entry_date',
        'good_quantity',
        'rejected_quantity',
        'rework_quantity',
        'scrap_quantity',
        'operator_id',
        'machine_id',
        'shift_id',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'entry_date' => 'datetime',
        'good_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
        'rework_quantity' => 'decimal:4',
        'scrap_quantity' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function outputs(): HasMany
    {
        return $this->hasMany(ProductionEntryOutput::class);
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }
}