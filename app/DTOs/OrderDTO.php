<?php

namespace App\DTOs;

use App\Contracts\CartServiceInterface;

readonly class OrderDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public int $totalQty,
        public float $totalSum,
        public string $currency,
        public array $items
    ) {}

    public static function fromRequest(array $data, array $cart, CartServiceInterface $cartService): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            totalQty: $cartService->getTotalQuantity($cart),
            totalSum: $cartService->getTotalSum($cart),
            currency: 'USD',
            items: array_map(fn($item) => OrderItemDTO::fromArray($item), $cart)
        );
    }
}
