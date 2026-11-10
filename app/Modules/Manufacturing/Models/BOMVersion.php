<?php

namespace App\Modules\Manufacturing\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BOMVersion extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'bom_header_id',
        'tenant_id',
        'version',
        'effective_from',
        'effective_to',
        'is_active',
        'standard_material_cost',
        'notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'standard_material_cost' => 'decimal:4',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(BOMHeader::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(BOMComponent::class, 'bom_version_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(BOMOperation::class, 'bom_version_id');
    }
}