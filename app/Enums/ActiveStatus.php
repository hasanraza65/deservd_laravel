<?php

namespace App\Enums;

/** Generic active/inactive switch shared by categories, coupons, and other simple toggles. */
enum ActiveStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
