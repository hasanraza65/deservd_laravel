<?php

namespace App\Enums;

enum ShippingMethodType: string
{
    case Shipping = 'shipping';
    case Pickup = 'pickup';
}
