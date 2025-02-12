<?php

namespace App\Contracts;

interface CartServiceInterface
{
    public function getProducts(): array;
    public function getTotalQuantity(array $products): int;
    public function getTotalSum(array $products): float;
}
