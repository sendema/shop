<?php

namespace App\Services;

use App\Contracts\CartServiceInterface;

class CartService implements CartServiceInterface
{
    public function getProducts(): array
    {
        return [
            [
                'name_product' => 'Скрепки',
                'price' => 20,
                'qty' => 2,
            ],
            [
                'name_product' => 'Шариковая ручка',
                'price' => 55.5,
                'qty' => 5,
            ]
        ];
    }

    public function getTotalQuantity(array $products): int
    {
        return array_sum(array_column($products, 'qty'));
    }

    public function getTotalSum(array $products): float
    {
        return array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $products));
    }
}
