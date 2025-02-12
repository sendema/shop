<?php

namespace App\Contracts;

interface CartServiceInterface
{
    public function getCartData(): array;
    public function getTotalQuantity(array $cart): int;
    public function getTotalSum(array $cart): float;
}
