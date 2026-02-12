<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterableMovement
{
    public function scopeForecast(Builder $query): Builder
    {
        return $query->where('is_forecast', true);
    }

    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_forecast', false);
    }

    public function scopeType(Builder $query, bool $isForecast): Builder
    {
        return $query->where('is_forecast', $isForecast);
    }
}
