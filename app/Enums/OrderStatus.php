<?php

namespace App\Enums;

/**
 * The bakery fulfilment workflow. Deliberately separate from PaymentStatus —
 * an order can be PAID and still be PREPARING, or CANCELLED after payment
 * (refund handled via PaymentStatus::Refunded).
 */
enum OrderStatus: string
{
    case New = 'new';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Baked = 'baked';
    case Packaged = 'packaged';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Confirmed => 'Confirmed',
            self::Preparing => 'Preparing',
            self::Baked => 'Baked',
            self::Packaged => 'Packaged',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    /** The linear happy-path steps shown as a progress tracker in the admin UI. */
    public static function workflowSteps(): array
    {
        return [self::Confirmed, self::Preparing, self::Baked, self::Packaged, self::Shipped, self::Delivered];
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled, self::Refunded], true);
    }
}
