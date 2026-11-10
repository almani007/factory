<?php

namespace App\Modules\Products\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use App\Modules\Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'category_id',
        'sku',
        'name',
        'description',
        'brand',
        'unit_id',
        'barcode',
        'image',
        'specifications',
        'min_stock',
        'reorder_level',
        'preferred_supplier_id',
        'default_purchase_price',
        'standard_production_cost',
        'is_active',
        'track_batch',
        'track_serial',
    ];

    protected $casts = [
        'specifications' => 'array',
        'min_stock' => 'decimal:4',
        'reorder_level' => 'decimal:4',
        'default_purchase_price' => 'decimal:4',
        'standard_production_cost' => 'decimal:4',
        'is_active' => 'boolean',
        'track_batch' => 'boolean',
        'track_serial' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ItemVariant::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(\App\Modules\Inventory\Models\StockMovement::class, 'item_id');
    }
}