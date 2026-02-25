<?php

namespace App\Domain\Orders;

use Filament\Schemas\Components\Utilities\Get;

class OrderCalculationInput
{
    public function __construct(
        public readonly ?int $sizeId,
        public readonly int $paperCirculation,
        public readonly ?int $typePaperId,
        public readonly ?string $typeOrder,
        public readonly array $printOptions,
        public readonly array $printType,
        public readonly ?string $printTypeOfset,
        public readonly ?string $printTypeOfset2,
        public readonly ?string $printTypeSelkografiia,
        public readonly array $postPrintType,
        public readonly ?string $typeLamination,
        public readonly bool $printPlashka,
        public readonly bool $printOptionDischarge,
        public readonly ?string $typeBracingHandle,
        public readonly ?int $typeHandleId,
        public readonly bool $handleX2,
    ) {
    }

    public static function fromGet(Get $get): self
    {
        return new self(
            sizeId: self::intOrNull($get('size_id')),
            paperCirculation: self::intOrZero($get('paper_circulation')),
            typePaperId: self::intOrNull($get('type_paper')),
            typeOrder: self::stringOrNull($get('type_order')),
            printOptions: self::arrayOrEmpty($get('print_options')),
            printType: self::arrayOrEmpty($get('print_type')),
            printTypeOfset: self::stringOrNull($get('print_type_ofset')),
            printTypeOfset2: self::stringOrNull($get('print_type_ofset_2')),
            printTypeSelkografiia: self::stringOrNull($get('print_type_selkografiia')),
            postPrintType: self::arrayOrEmpty($get('post_print_type')),
            typeLamination: self::stringOrNull($get('type_lamination')),
            printPlashka: (bool) $get('print_plashka'),
            printOptionDischarge: (bool) $get('print_option_discharge'),
            typeBracingHandle: self::stringOrNull($get('type_bracing_handle')),
            typeHandleId: self::intOrNull($get('type_handle')),
            handleX2: (bool) $get('handle_x2'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sizeId: self::intOrNull($data['size_id'] ?? null),
            paperCirculation: self::intOrZero($data['paper_circulation'] ?? null),
            typePaperId: self::intOrNull($data['type_paper'] ?? null),
            typeOrder: self::stringOrNull($data['type_order'] ?? null),
            printOptions: self::arrayOrEmpty($data['print_options'] ?? []),
            printType: self::arrayOrEmpty($data['print_type'] ?? []),
            printTypeOfset: self::stringOrNull($data['print_type_ofset'] ?? null),
            printTypeOfset2: self::stringOrNull($data['print_type_ofset_2'] ?? null),
            printTypeSelkografiia: self::stringOrNull($data['print_type_selkografiia'] ?? null),
            postPrintType: self::arrayOrEmpty($data['post_print_type'] ?? []),
            typeLamination: self::stringOrNull($data['type_lamination'] ?? null),
            printPlashka: (bool) ($data['print_plashka'] ?? false),
            printOptionDischarge: (bool) ($data['print_option_discharge'] ?? false),
            typeBracingHandle: self::stringOrNull($data['type_bracing_handle'] ?? null),
            typeHandleId: self::intOrNull($data['type_handle'] ?? null),
            handleX2: (bool) ($data['handle_x2'] ?? false),
        );
    }

    private static function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private static function intOrZero(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (int) $value;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private static function arrayOrEmpty(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return [];
        }

        return [(string) $value];
    }
}
