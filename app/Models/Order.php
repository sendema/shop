<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChangedEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'name',
        'email',
        'qty',
        'sum',
        'currency',
        'status',
        'token',
    ];

    protected $casts = [
        'status' => OrderStatus::class
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_order');
    }

    protected static function booted()
    {
        static::created(function ($order) {
            event(new OrderCreatedEvent($order));
        });

        static::updated(function ($order) {
            if ($order->wasChanged('status')) {
                event(new OrderStatusChangedEvent($order, $order->status));
            }
        });
    }
}
