<?php

namespace App\DTOs;

use App\Http\Requests\OrderRequest;

readonly class OrderUserDataDTO
{
    public function __construct(
        public string $name,
        public string $email
    ) {}

    public static function fromRequest(OrderRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email')
        );
    }
}
