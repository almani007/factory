<?php

namespace App\Modules\HR\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use App\Modules\Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'employee_code',
        'name',
        'email',
        'phone',
        'department_id',
        'designation',
        'hire_date',
        'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'is_active' => 'boolean',
    ];
}