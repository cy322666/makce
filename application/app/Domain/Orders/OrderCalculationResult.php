<?php

namespace App\Domain\Orders;

class OrderCalculationResult
{
    public function __construct(
        public readonly array $metrics,
        public readonly array $components,
    ) {
    }

    public function value(string $key, mixed $default = 0): mixed
    {
        return $this->metrics[$key] ?? $default;
    }

    public function toArray(): array
    {
        return [
            'metrics' => $this->metrics,
            'components' => $this->components,
        ];
    }
}
