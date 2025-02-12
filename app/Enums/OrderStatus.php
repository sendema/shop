<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PENDING = 0;
    case PAID = 1;
    case CANCELLED = 2;

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::PAID => 'green',
            self::CANCELLED => 'red',
        };
    }
}
