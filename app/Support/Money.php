<?php

namespace App\Support;

/**
 * All monetary amounts are stored in the database as integer cents to avoid
 * floating-point rounding drift in discount/tax/total arithmetic. This class
 * is the one place that converts between that storage format and the
 * decimal-dollar values the API contract (and the React frontend) expects.
 */
class Money
{
    public static function toCents(float|int|string $dollars): int
    {
        return (int) round(((float) $dollars) * 100);
    }

    public static function toDollars(int $cents): float
    {
        return round($cents / 100, 2);
    }
}
