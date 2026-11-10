<?php

namespace App\Modules\Quality\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use App\Modules\Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityInspection extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    const STATUS_PENDING = 'pending';
    const STATUS_PASSED = 'passed';
    const STATUS_FAILED = 'failed';
    const STATUS_PARTIAL = 'partially_accepted';
    const STATUS_REWORK = 'rework_required';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'inspection_template_id',
        'inspection_type',
        'reference_type',
        'reference_id',
        'status',
        'inspected_by',
        'inspected_at',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(QualityInspectionResult::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class);
    }
}