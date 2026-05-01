<?php

namespace App\Filament\Pages\PaperBagCalculatorV2\Schemas;

use App\Domain\BagCalculatorV2\BagCalculationInput;
use App\Domain\BagCalculatorV2\BagCalculator;
use App\Domain\BagCalculatorV2\BagCalculationResult;
use App\Models\Category;
use App\Models\Ofset;
use App\Models\PaperPrice;
use App\Models\Size;
use App\Models\Group;
use App\Models\LacPrice;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\HtmlString;
use Livewire\Component as LivewireComponent;

class PaperBagCalculatorV2Form
{
    private static array $cache = [];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)->schema([
                    Section::make('Ввод')
                        ->description('Отдельная v2-версия по формулам клиента. Старый калькулятор не меняется.')
                        ->schema(static::inputComponents())
                        ->columnSpan(['lg' => 7])
                        ->columns(2),

                    Section::make('Итоги')
                        ->schema(static::resultComponents())
                        ->columnSpan(['lg' => 5])
                        ->columns(2),

                    Section::make('Разбивка по узлам')
                        ->schema(static::breakdownComponents())
                        ->columnSpanFull()
                        ->columns(3),

                    Section::make('Прайсы из базы')
                        ->schema(static::priceSourceComponents())
                        ->columnSpanFull()
                        ->columns(2),

                    Section::make('Отладка')
                        ->schema(static::debugComponents())
                        ->columnSpanFull()
                        ->columns(1)
                        ->collapsible(),
                ]),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    private static function inputComponents(): array
    {
        return [
            Section::make('Основные параметры')
                ->schema([
                    TextInput::make('paper_circulation')
                        ->label('Тираж')
                        ->numeric()
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),

                    Select::make('size_id')
                        ->label('Форма')
                        ->searchable(['number', 'size'])
                        ->preload()
                        ->live()
                        ->options(static::sizeOptions())
                        ->required()
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),

                    Select::make('paper_price_id')
                        ->label('Бумага')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->options(static::paperOptions())
                        ->required()
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),

                    Select::make('type_order')
                        ->label('Тип заказа')
                        ->live()
                        ->options([
                            'direct' => 'Прямой',
                            'agency' => 'Агентство',
                        ])
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Печать')
                ->schema([
                    Select::make('print_type')
                        ->label('Тип печати')
                        ->multiple()
                        ->live()
                        ->options([
                            'ofset' => 'Офсет',
                            'selkografiia' => 'Шелкография',
                        ])
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),

                    Select::make('offset_price_id')
                        ->label('Строка офсета')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->options(static::offsetOptions())
                        ->visible(fn (Get $get): bool => in_array('ofset', (array) $get('print_type'), true))
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),

                    Select::make('silk_color_count')
                        ->label('Цветов шелкографии')
                        ->live()
                        ->options([
                            1 => '1 цвет',
                            2 => '2 цвета',
                            3 => '3 цвета',
                            4 => '4 цвета',
                        ])
                        ->visible(fn (Get $get): bool => in_array('selkografiia', (array) $get('print_type'), true))
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),

                    Select::make('print_plashka')
                        ->label('Плашка')
                        ->live()
                        ->options([
                            0 => 'Нет',
                            1 => 'Да',
                        ])
                        ->default(0)
                        ->visible(fn (Get $get): bool => in_array('ofset', (array) $get('print_type'), true))
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Постпечатка')
                ->schema([
                    Select::make('print_option_discharge')
                        ->label('Коронирование')
                        ->live()
                        ->options([
                            0 => 'Нет',
                            1 => 'Да',
                        ])
                        ->default(0)
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),

                    Select::make('post_print_type')
                        ->label('Постпечатка')
                        ->multiple()
                        ->live()
                        ->options([
                            'uf-lak' => 'Выборочный УФ-лак',
                        ])
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Ламинация')
                ->schema([
                    Select::make('type_lamination')
                        ->label('Ламинация')
                        ->live()
                        ->options(static::laminationOptions())
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Ручка')
                ->schema([
                    Select::make('type_handle_id')
                        ->label('Ручка')
                        ->live()
                        ->searchable()
                        ->preload()
                        ->options(static::handleOptions())
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),

                    Select::make('type_bracing_handle')
                        ->label('Крепление ручки')
                        ->live()
                        ->options(static::bracingHandleOptions())
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),

                    Select::make('handle_x2')
                        ->label('Ручка х2')
                        ->live()
                        ->options([
                            0 => 'Нет',
                            1 => 'Да',
                        ])
                        ->default(0)
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Люверс')
                ->schema([
                    Select::make('luvers_enabled')
                        ->label('Люверс')
                        ->live()
                        ->options([
                            0 => 'Нет',
                            1 => 'Да',
                        ])
                        ->default(1)
                        ->afterStateUpdated(fn (LivewireComponent & HasSchemas $livewire) => $livewire->syncCalculation()),
                ])
                ->columns(1)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function resultComponents(): array
    {
        return [
            TextEntry::make('paper_total')
                ->label('Бумага')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('paper_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('offset_total')
                ->label('Офсет')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('offset_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('silk_total')
                ->label('Шелкография')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('silk_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('uv_lac_total')
                ->label('УФ-лак')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('uv_lac_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('lamination_total')
                ->label('Ламинация')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('lamination_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('die_cutting_total')
                ->label('Вырубка')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('die_cutting_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('cutting_total')
                ->label('Резка')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('cutting_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('handle_total')
                ->label('Ручка')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('handle_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('assembly_total')
                ->label('Сборка')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('assembly_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('bottom_total')
                ->label('Дно')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('bottom_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('luvers_total')
                ->label('Люверс')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('luvers_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('packaging_total')
                ->label('Упаковка')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('packaging_total', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('result_materials')
                ->label('Стоимость материалов')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('result_materials', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('result_works')
                ->label('Стоимость работ')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('result_works', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('subtotal')
                ->label('Себестоимость')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('subtotal', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('markup_percent')
                ->label('Наценка %')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('markup_percent', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('result_circulation')
                ->label('Итого')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('result_circulation', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),

            TextEntry::make('price_per_bag')
                ->label('Цена 1 пакета')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('price_per_bag', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->weight(FontWeight::Bold)
                ->size(TextSize::Large),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function breakdownComponents(): array
    {
        return [
            TextEntry::make('paper_sheet_count')
                ->label('Листов по тиражу')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('paper_sheet_count', $livewire))
                ->fontFamily(FontFamily::Mono),

            TextEntry::make('paper_prebuild_sheets')
                ->label('Приладка печати')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('paper_prebuild_sheets', $livewire))
                ->fontFamily(FontFamily::Mono),

            TextEntry::make('paper_sheets_in_work')
                ->label('Листов в работу')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('paper_sheets_in_work', $livewire))
                ->fontFamily(FontFamily::Mono),

            TextEntry::make('paper_price')
                ->label('Цена 1 листа')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::moneyFromLivewire('paper_price', $livewire))
                ->fontFamily(FontFamily::Mono),

            TextEntry::make('paper_source_detail')
                ->label('Бумага из базы')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::valueFromLivewire('paper_source', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),

            TextEntry::make('offset_source_detail')
                ->label('Офсет из базы')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::valueFromLivewire('offset_source', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),

            TextEntry::make('uv_lac_source_detail')
                ->label('УФ-лак из базы')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::valueFromLivewire('uv_lac_source', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),

            TextEntry::make('lamination_source_detail')
                ->label('Ламинация из базы')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::valueFromLivewire('lamination_source', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),

            TextEntry::make('handle_source_detail')
                ->label('Ручка из базы')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::valueFromLivewire('handle_source', $livewire))
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function priceSourceComponents(): array
    {
        return [
            TextEntry::make('paper_source_price')
                ->label('Бумага')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::valueFromLivewire('paper_source', $livewire))
                ->columnSpanFull()
                ->fontFamily(FontFamily::Mono),

            TextEntry::make('offset_source_price')
                ->label('Офсет')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::valueFromLivewire('offset_source', $livewire))
                ->columnSpanFull()
                ->fontFamily(FontFamily::Mono),

            TextEntry::make('lamination_source_price')
                ->label('Ламинация')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::valueFromLivewire('lamination_source', $livewire))
                ->columnSpanFull()
                ->fontFamily(FontFamily::Mono),

            TextEntry::make('uv_lac_source_price')
                ->label('УФ-лак')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::valueFromLivewire('uv_lac_source', $livewire))
                ->columnSpanFull()
                ->fontFamily(FontFamily::Mono),

            TextEntry::make('handle_source_price')
                ->label('Ручка')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::valueFromLivewire('handle_source', $livewire))
                ->columnSpanFull()
                ->fontFamily(FontFamily::Mono),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function debugComponents(): array
    {
        return [
            TextEntry::make('debug_summary')
                ->label('[Отладка] Сводка')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::debugFromLivewire('summary', $livewire))
                ->formatStateUsing(fn (?string $state) => new HtmlString(nl2br(e($state ?? ''))))
                ->html()
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),

            TextEntry::make('debug_nodes')
                ->label('[Отладка] Основные узлы')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::debugFromLivewire('nodes', $livewire))
                ->formatStateUsing(fn (?string $state) => new HtmlString(nl2br(e($state ?? ''))))
                ->html()
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),

            TextEntry::make('debug_formulas')
                ->label('[Отладка] Формулы')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::debugFromLivewire('formulas', $livewire))
                ->formatStateUsing(fn (?string $state) => new HtmlString(nl2br(e($state ?? ''))))
                ->html()
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),

            TextEntry::make('debug_inputs')
                ->label('[Отладка] Входные параметры')
                ->state(fn (LivewireComponent & HasSchemas $livewire) => static::debugFromLivewire('inputs', $livewire))
                ->formatStateUsing(fn (?string $state) => new HtmlString(nl2br(e($state ?? ''))))
                ->html()
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function paperOptions(): array
    {
        return PaperPrice::query()
            ->orderBy('group_name')
            ->orderBy('sheet_format')
            ->orderBy('title')
            ->get()
            ->mapWithKeys(static function (PaperPrice $paper): array {
                $sale = (float) ($paper->sale_price ?? 0);

                if ($sale <= 0 && (float) $paper->base_price > 0) {
                    $sale = (float) $paper->base_price * (1 + ((float) $paper->markup_percent / 100));
                }

                $label = trim(
                    ($paper->group_name ?: 'Без группы') .
                    ' / ' . $paper->title .
                    ' / ' . $paper->sheet_format .
                    ' / ' . number_format($sale, 2, '.', ' ')
                );

                return [$paper->id => $label];
            })
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function sizeOptions(): array
    {
        return Size::query()
            ->orderBy('number')
            ->get()
            ->mapWithKeys(static function (Size $size): array {
                return [
                    $size->id => trim(
                        '№ ' . $size->number .
                        ' / ' . ($size->type ?: '-') .
                        ' / ' . ($size->count_blank ?: 1) . ' шт'
                    ),
                ];
            })
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function offsetOptions(): array
    {
        return Ofset::query()
            ->orderBy('colors')
            ->get()
            ->mapWithKeys(static function (Ofset $ofset): array {
                $setup = (float) ($ofset->sale_preparation ?? 0);
                $sheet = (float) ($ofset->sale_print_mel_paper ?? $ofset->sale_print ?? 0);

                return [
                    $ofset->id => trim(
                        ($ofset->colors ?: '-') .
                        ' / подг. ' . number_format($setup, 2, '.', ' ') .
                        ' / лист ' . number_format($sheet, 2, '.', ' ')
                    ),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function laminationOptions(): array
    {
        return Category::getChildrens('Тип ламинации')
            ->pluck('name', 'slug')
            ->toArray();
    }

    /**
     * @return array<int, string>
     */
    private static function handleOptions(): array
    {
        return Category::getChildrens('Тип ручки')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * @return array<int, string>
     */
    private static function bracingHandleOptions(): array
    {
        return Category::getChildrens('Тип крепления ручки')
            ->pluck('name', 'slug')
            ->toArray();
    }

    private static function calculate(BagCalculationInput $input): BagCalculationResult
    {
        $cacheKey = md5(json_encode($input->toArray(), JSON_UNESCAPED_UNICODE) ?: serialize($input->toArray()));

        if (! array_key_exists($cacheKey, self::$cache)) {
            /** @var BagCalculator $calculator */
            $calculator = app(BagCalculator::class);
            self::$cache[$cacheKey] = $calculator->calculate($input);
        }

        return self::$cache[$cacheKey];
    }

    private static function metric(string $key, Get $get): mixed
    {
        return static::calculate(BagCalculationInput::fromGet($get))->value($key);
    }

    private static function metricFromLivewire(string $key, LivewireComponent & HasSchemas $livewire): mixed
    {
        return data_get($livewire, 'calculation.metrics.' . $key, 0);
    }

    private static function value(string $key, Get $get): mixed
    {
        return static::metric($key, $get);
    }

    private static function valueFromLivewire(string $key, LivewireComponent & HasSchemas $livewire): mixed
    {
        return static::metricFromLivewire($key, $livewire);
    }

    private static function money(string $key, Get $get): string
    {
        return number_format((float) static::metric($key, $get), 2, '.', ' ');
    }

    private static function moneyFromLivewire(string $key, LivewireComponent & HasSchemas $livewire): string
    {
        return number_format((float) static::metricFromLivewire($key, $livewire), 2, '.', ' ');
    }

    private static function debugValue(string $key, Get $get): string
    {
        $debug = static::calculate(BagCalculationInput::fromGet($get))->debug();

        return (string) ($debug[$key] ?? '');
    }

    private static function debugFromLivewire(string $key, LivewireComponent & HasSchemas $livewire): string
    {
        return (string) data_get($livewire, 'calculation.debug.' . $key, '');
    }
}
