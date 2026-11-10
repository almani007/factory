<?php

namespace Manufacturing\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BOMHeader extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'item_id',
        'name',
        'description',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(\Products\Models\Item::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(BOMVersion::class, 'bom_header_id');
    }

    public function currentVersion()
    {
        return $this->versions()->where('is_active', true)->first();
    }
}