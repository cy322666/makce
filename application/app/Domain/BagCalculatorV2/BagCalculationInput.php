<?php

namespace App\Domain\BagCalculatorV2;

use Filament\Schemas\Components\Utilities\Get;

final class BagCalculationInput
{
    public function __construct(
        public readonly ?int $paperPriceId,
        public readonly ?int $sizeId,
        public readonly int $paperCirculation,
        public readonly ?string $typeOrder,
        public readonly bool $calcPrint,
        public readonly bool $calcLamination,
        public readonly bool $calcPostPrint,
        public readonly bool $calcHandle,
        public readonly bool $calcLuvers,
        public readonly array $printOptions,
        public readonly array $printType,
        public readonly ?int $offsetPriceId,
        public readonly ?int $silkColorCount,
        public readonly array $postPrintType,
        public readonly ?string $typeLamination,
        public readonly bool $printPlashka,
        public readonly bool $printOptionDischarge,
        public readonly ?int $typeHandleId,
        public readonly ?string $typeBracingHandle,
        public readonly bool $luversEnabled,
        public readonly bool $handleX2,
    ) {}

    public static function fromGet(Get $get): self
    {
        return self::fromArray(self::stateFromGet($get));
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function fromState(array $state): self
    {
        return new self(
            paperPriceId: self::intOrNull($state['paper_price_id'] ?? null),
            sizeId: self::intOrNull($state['size_id'] ?? null),
            paperCirculation: max(0, (int) ($state['paper_circulation'] ?? 0)),
            typeOrder: self::stringOrNull($state['type_order'] ?? null),
            calcPrint: (bool) ($state['calc_print'] ?? false),
            calcLamination: (bool) ($state['calc_lamination'] ?? false),
            calcPostPrint: (bool) ($state['calc_post_print'] ?? false),
            calcHandle: (bool) ($state['calc_handle'] ?? false),
            calcLuvers: (bool) ($state['calc_luvers'] ?? false),
            printOptions: self::selectedOptionsFromState($state),
            printType: self::arrayOrEmpty($state['print_type'] ?? null),
            offsetPriceId: self::intOrNull($state['offset_price_id'] ?? null),
            silkColorCount: self::intOrNull($state['silk_color_count'] ?? null),
            postPrintType: self::arrayOrEmpty($state['post_print_type'] ?? null),
            typeLamination: self::stringOrNull($state['type_lamination'] ?? null),
            printPlashka: (bool) ($state['print_plashka'] ?? false),
            printOptionDischarge: (bool) ($state['print_option_discharge'] ?? false),
            typeHandleId: self::intOrNull($state['type_handle_id'] ?? null),
            typeBracingHandle: self::stringOrNull($state['type_bracing_handle'] ?? null),
            luversEnabled: self::boolFromSelect($state['luvers_enabled'] ?? null, true),
            handleX2: (bool) ($state['handle_x2'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'paperPriceId' => $this->paperPriceId,
            'sizeId' => $this->sizeId,
            'paperCirculation' => $this->paperCirculation,
            'typeOrder' => $this->typeOrder,
            'calcPrint' => $this->calcPrint,
            'calcLamination' => $this->calcLamination,
            'calcPostPrint' => $this->calcPostPrint,
            'calcHandle' => $this->calcHandle,
            'calcLuvers' => $this->calcLuvers,
            'printOptions' => $this->printOptions,
            'printType' => $this->printType,
            'offsetPriceId' => $this->offsetPriceId,
            'silkColorCount' => $this->silkColorCount,
            'postPrintType' => $this->postPrintType,
            'typeLamination' => $this->typeLamination,
            'printPlashka' => $this->printPlashka,
            'printOptionDischarge' => $this->printOptionDischarge,
            'typeHandleId' => $this->typeHandleId,
            'typeBracingHandle' => $this->typeBracingHandle,
            'luversEnabled' => $this->luversEnabled,
            'handleX2' => $this->handleX2,
        ];
    }

    private static function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<int, string>
     */
    private static function arrayOrEmpty(mixed $value): array
    {
        if (! is_array($value)) {
            if ($value === null || $value === '') {
                return [];
            }

            return [(string) $value];
        }

        return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $value), static fn (string $item): bool => $item !== ''));
    }

    private static function boolFromSelect(mixed $value, bool $default = false): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @return array<int, string>
     */
    private static function selectedOptions(Get $get): array
    {
        return self::selectedOptionsFromState(self::stateFromGet($get));
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<int, string>
     */
    private static function selectedOptionsFromState(array $state): array
    {
        $options = [];

        if (self::arrayOrEmpty($state['print_type'] ?? null) !== []) {
            $options[] = 'print';
        }

        if (self::stringOrNull($state['type_lamination'] ?? null) !== null) {
            $options[] = 'lamination';
        }

        if (self::arrayOrEmpty($state['post_print_type'] ?? null) !== [] || (bool) ($state['print_option_discharge'] ?? false)) {
            $options[] = 'post_print';
        }

        if (self::intOrNull($state['type_handle_id'] ?? null) !== null) {
            $options[] = 'handle';
        }

        if (self::boolFromSelect($state['luvers_enabled'] ?? null, true)) {
            $options[] = 'luvers';
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     */
    private static function stateFromGet(Get $get): array
    {
        return [
            'paper_price_id' => $get('paper_price_id'),
            'size_id' => $get('size_id'),
            'paper_circulation' => $get('paper_circulation'),
            'type_order' => $get('type_order'),
            'calc_print' => $get('calc_print'),
            'calc_lamination' => $get('calc_lamination'),
            'calc_post_print' => $get('calc_post_print'),
            'calc_handle' => $get('calc_handle'),
            'calc_luvers' => $get('calc_luvers'),
            'print_type' => $get('print_type'),
            'offset_price_id' => $get('offset_price_id'),
            'silk_color_count' => $get('silk_color_count'),
            'post_print_type' => $get('post_print_type'),
            'type_lamination' => $get('type_lamination'),
            'print_plashka' => $get('print_plashka'),
            'print_option_discharge' => $get('print_option_discharge'),
            'type_handle_id' => $get('type_handle_id'),
            'type_bracing_handle' => $get('type_bracing_handle'),
            'luvers_enabled' => $get('luvers_enabled'),
            'handle_x2' => $get('handle_x2'),
        ];
    }
}
