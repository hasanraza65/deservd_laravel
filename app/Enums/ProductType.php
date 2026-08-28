<?php

namespace App\Enums;

enum ProductType: string
{
    case Standard = 'standard';
    case DeservdXX = 'deservd_xx';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard Cookie',
            self::DeservdXX => "DESERV'D XX",
        };
    }
}
