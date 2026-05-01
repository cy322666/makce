<?php

namespace App\Domain\BagCalculatorV2;

final class BagCalculationResult
{
    /**
     * @param array<string, mixed> $metrics
     * @param array<string, array{materials: float, works: float, total: float}> $components
     * @param array<string, string> $debug
     */
    public function __construct(
        public readonly array $metrics,
        public readonly array $components,
        public readonly array $debug,
    ) {}

    public function value(string $key, mixed $default = null): mixed
    {
        return $this->metrics[$key] ?? $default;
    }

    /**
     * @return array<string, array{materials: float, works: float, total: float}>
     */
    public function components(): array
    {
        return $this->components;
    }

    /**
     * @return array<string, string>
     */
    public function debug(): array
    {
        return $this->debug;
    }
}
