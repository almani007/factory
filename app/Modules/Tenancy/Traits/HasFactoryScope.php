<?php

namespace App\Modules\Tenancy\Traits;

trait HasFactoryScope
{
    public static function bootHasFactoryScope(): void
    {
        static::addGlobalScope('factory', function ($builder) {
            $factoryId = request('factory_id') ?? request()->header('X-Factory-ID');

            if ($factoryId) {
                $builder->where('factory_id', $factoryId);
            }
        });
    }

    public function scopeForFactory($builder, int $factoryId)
    {
        return $builder->where('factory_id', $factoryId);
    }
}