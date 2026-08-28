<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class BoxOption extends Model
{
    protected $fillable = [
        'size',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => Money::toDollars($value),
            set: fn (float|int $value) => Money::toCents($value),
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
