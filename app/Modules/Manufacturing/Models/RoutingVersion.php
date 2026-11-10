<?php

namespace App\Modules\Manufacturing\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingVersion extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'routing_id',
        'tenant_id',
        'version',
        'effective_from',
        'effective_to',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function routing(): BelongsTo
    {
        return $this->belongsTo(Routing::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(RoutingOperation::class, 'routing_version_id')->orderBy('sequence');
    }
}