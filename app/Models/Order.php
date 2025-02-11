<?php

namespace App\Models;

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
    const STATUS_PENDING = 0;
    const STATUS_PAID = 1;
    const STATUS_CANCELLED = 2;

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_order');
    }
}
