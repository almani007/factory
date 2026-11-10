<?php

namespace App\Modules\Quality\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionTemplateItem extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'inspection_template_id',
        'tenant_id',
        'check_name',
        'check_type',
        'min_value',
        'max_value',
        'tolerance',
        'is_required',
        'sequence',
    ];

    protected $casts = [
        'min_value' => 'decimal:4',
        'max_value' => 'decimal:4',
        'tolerance' => 'decimal:4',
        'is_required' => 'boolean',
        'sequence' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class);
    }
}