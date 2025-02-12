<?php

namespace App\DTOs;

readonly class OrderItemDTO
{
    public function __construct(
        public string $name,
        public float $price,
        public int $qty
    ) {}

    public static function fromArray(array $item): self
    {
        return new self(
            name: $item['name_product'],
            price: $item['price'],
            qty: $item['qty']
        );
    }
}
