<?php

namespace App\DTOs;

class PayPalCaptureResponseDTO
{
    public function __construct(
        private string $status,
        private array $rawResponse
    ) {}

    public static function fromArray(array $response): self
    {
        return new self(
            status: $response['status'],
            rawResponse: $response
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }
}
