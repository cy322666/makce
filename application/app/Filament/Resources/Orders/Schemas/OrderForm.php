<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Orders\OrderCalculationInput;
use App\Domain\Orders\OrderCalculator;
use App\Models\Category;
use App\Models\Ofset;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class OrderForm
{
    //формат для листов на которых печатаем
    //если форма А1 формата, то тогда на нем
    public string $format = 'A2';

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([

                        Section::make('Настройка')
                            ->schema(static::getBaseComponents())
                            ->collapsible()
                            ->columnSpan(['lg' => 2])
                            ->columns(),

                    ]),//->columns(3)

                    Section::make('Итоги расчетов')
                        ->schema(static::getBaseResultComponents())
                        ->columns(2),

//                Section::make()
//                    ->schema([
//                        TextEntry::make('created_at')
//                            ->label('Order date')
//                            ->state(fn (Order $record): ?string => $record->created_at?->diffForHumans()),
//
//                        TextEntry::make('updated_at')
//                            ->label('Last modified at')
//                            ->state(fn (Order $record): ?string => $record->updated_at?->diffForHumans()),
//                    ])
//                    ->columnSpan(['lg' => 1])
//                    ->hidden(fn (?Order $record) => $record === null),
            ]);//->columns(3);

        //                        Section::make('Order items')
//                            ->headerActions([
//                                Action::make('reset')
//                                    ->modalHeading('Are you sure?')
//                                    ->modalDescription('All existing items will be removed from the order.')
//                                    ->requiresConfirmation()
//                                    ->color('danger')
////                                    ->action(fn (Set $set) => $set('items', [])),
//                            ])
//                            ->schema([
////                                static::getItemsRepeater(),
//                            ]),
    }

    //ИТОГИ БАЗА
    public static function getBaseResultComponents(): array
    {
        return [

            Section::make()
                ->schema([

                    TextEntry::make('result_1_circulation')
                        ->label('Итого за единицу')
                        ->dehydrated()
                        ->weight(FontWeight::Bold)
                        ->fontFamily(FontFamily::Mono)
                        ->state(fn (Get $get): ?string => $get('paper_circulation') && $get('result_circulation') ? $get('result_circulation') / $get('paper_circulation') : 0)
                        ->size(TextSize::Large),

                    TextEntry::make('result_circulation')
                        ->label('Итого за тираж')
                        ->dehydrated()
                        ->weight(FontWeight::Bold)
                        ->fontFamily(FontFamily::Mono)
                        ->state(fn (Get $get): ?string => (string) static::metric('result_circulation', $get))
                        ->size(TextSize::Large),
                ]),

            Section::make()
                ->schema([
                    TextEntry::make('result_materials')
                        ->label('Итого на все материалы')
                        ->dehydrated()
                        ->weight(FontWeight::Bold)
                        ->fontFamily(FontFamily::Mono)
                        ->state(fn (Get $get): ?string => (string) static::metric('result_materials', $get))
                        ->size(TextSize::Large),

                    TextEntry::make('result_works')
                        ->label('Итого на все работы')
                        ->dehydrated()
                        ->weight(FontWeight::Bold)
                        ->fontFamily(FontFamily::Mono)
                        ->state(fn (Get $get): ?string => (string) static::metric('result_works', $get))
                        ->size(TextSize::Large),
                ]),

            TextEntry::make('group_name')
                ->label('Группа формы')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get): ?string => (string) static::metric('group_name', $get))
                ->size(TextSize::Large),

            TextEntry::make('markup')//наценка //TODO не участвует в сумме
                ->label('Наценка %')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get): ?string => (string) static::metric('markup', $get))
                ->size(TextSize::Large),

            TextEntry::make('size_name')
                ->label('Номер формы')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get): ?string => (string) static::metric('size_name', $get))
                ->size(TextSize::Large),

            TextEntry::make('size_format')
                ->label('Формат листа печати')
                ->placeholder('На каком формате печатаем')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get): ?string => (string) static::metric('size_format', $get))
                ->size(TextSize::Large),

            TextEntry::make('group_info')
                ->label('Составляющие сборки')
                ->dehydrated()
//                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get): ?string => (string) static::metric('group_info', $get))
                ->size(TextSize::Large),

            TextEntry::make('paper_result')
                ->label('Итог на бумагу')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get): ?string => (string) static::metric('paper_result', $get))
                ->size(TextSize::Large),

            //общая стоиомсть сборки тиража
            TextEntry::make('assembly_result_circulation')
                ->label('Итого за сборку')
                ->dehydrated()
                ->reactive()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get): ?int => (int) static::metric('assembly_result_circulation', $get))
                ->size(TextSize::Large),

            TextEntry::make('print_prebuild')
                ->label('Листов на приладку печати')
                ->dehydrated()
                ->reactive()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get) => static::metric('print_prebuild', $get))
                ->size(TextSize::Large),

            TextEntry::make('paper_pack_work')
                ->label('Пакетов в работу')
                ->state(fn (Get $get) => round((float) static::metric('paper_pack_work', $get)))
                ->fontFamily(FontFamily::Mono)
                ->size(TextSize::Large)
                ->dehydrated(),

            TextEntry::make('lamination_result')
                ->label('Ламинация (с работой)')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get) => static::metric('lamination_result', $get))
                ->size(TextSize::Large),

            //-----

            TextEntry::make('count_paper')
                ->label('Листов в работу')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get) => static::metric('count_paper', $get))
                ->size(TextSize::Large),

            TextEntry::make('packages_result')
                ->label('Сумма за упаковку')
                ->state(fn (Get $get): ?int => (int) static::metric('packages_result', $get))
                ->size(TextSize::Large)
                ->fontFamily(FontFamily::Mono)
                ->dehydrated(),

//            TextEntry::make('print_sale_cutting')
//                ->label('Сумма за резку')
//                ->state(fn (Get $get): ?string => static::calcBase('print_sale_cutting', $get))
//                ->size(TextSize::Large)
//                ->fontFamily(FontFamily::Mono)
//                ->dehydrated(),

            TextEntry::make('print_sale_result')
                ->label('Стоимость печати')
                ->dehydrated()
                ->state(fn (Get $get): ?string => (string) static::metric('print_sale_result', $get))
                ->size(TextSize::Large)
                ->fontFamily(FontFamily::Mono)
                ->dehydrated(),

            TextEntry::make('post_print_sale')
                ->label('Стоимость пп печати (уф л)')
                ->state(fn (Get $get): ?string => (string) static::metric('post_print_sale', $get))
                ->size(TextSize::Large)
                ->fontFamily(FontFamily::Mono)
                ->dehydrated(),

            //ошибка
//            TextEntry::make('panton_result')
//                ->label('Итого за пантоны')
//                ->state(fn (Get $get): ?string => static::calcBase('panton_result', $get))
//                ->size(TextSize::Large)
//                ->fontFamily(FontFamily::Mono)
//                ->dehydrated(),

            TextEntry::make('assembly_result')
                ->label('Итого за резку')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get): ?int => (int) static::metric('assembly_result', $get))
                ->size(TextSize::Large),

            //стоимость материалов для ручек
            TextEntry::make('handle_result')
                ->label('Итого за ручки')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get) => static::metric('handle_result', $get))
                ->size(TextSize::Large),

            TextEntry::make('felling_result')
                ->label('Итого за вырубку')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->fontFamily(FontFamily::Mono)
                ->state(fn (Get $get): ?string => (string) static::metric('felling_result', $get))
                ->size(TextSize::Large),

            TextEntry::make('print_pages_circulation')
                ->label('Листов на печать тиража (без приладок и тд')
                ->dehydrated()
                ->weight(FontWeight::Bold)
                ->state(fn (Get $get) => static::metric('print_pages_circulation', $get))
                ->fontFamily(FontFamily::Mono)
                ->default(1)
                ->size(TextSize::Large),

//            TextEntry::make('print_sale_plates')
//                ->label('Стоимость пластин')
//                ->dehydrated()
//                ->size(TextSize::Large)
//                ->fontFamily(FontFamily::Mono)
//                ->state(fn (Get $get): ?string => static::calcBase('print_sale_plates', $get))
        ];
    }

    //мен выбирает тип бумаги, тираж, печать (цветность)
    // затем выбирает тип печати (офсет)
    // постпечатная обработка (теснение, шелкография, уэфлак, конгреф)

    //БУМАГА

    //выбираем тиражность и размер -> сколько пакетов, сколько на приладку
    //приладка : 50 а2 на 1 лист, 25 а1 на 1 лист (если обычный цвет)
    //если есть плашка, то х2 если 1 цвет и 1 плашка 100
    //если 3 цвета то все равно 50

    //закладываем ток на офсетную приладку, то добавляем бумагу


    //!!!бумага - тираж + приладка производство + приладка на печать (если есть)


    //ПЕЧАТЬ

    //офсет формула просто из таблы

    //колво цветов * 450 (стоимость фиксы надо мочь задавать)


    //пантоны нужны или нет, поле

    //нужна возможность добавлять коэфф при тяжелой бумаге или пакете, либо просто своя уник логика

    //пакеты по ширине пленки, есть файл

    //ЛАМИНАЦИЯ
    //д * ш * курс * cебес * тираж (множитель плотности, у него есть)

    //раз в 6к приладка (стоимость фиксированная)
    // Ламинация (всегда - только на мелованных пакетах)

    //ВЫРУБКА

    //для них:
    //ламинация
    //вырубка
    //печать
    //если больше 3000 то вторая приладка и шаг в 3к далее

    //люверс всегда одинаковый


    //менеджер видит только стоимость единицы и стоимость тиража

    //РАСШИРЕННАЯ

    //процент накрутки
    //стоимость материалов
    //бумага и прочее

    //19.10 - 34 часа + 9 + 4 + 4 + 4
    public static function getBaseComponents(): array
    {
        return [

            Section::make('Введите значения')
                ->schema([

                    TextInput::make('paper_circulation')
                        ->label('Тираж')
                        ->integer()
                        ->reactive()
                        ->required(),

                    //paper_size
                    Select::make('size_id')
                        ->label('Номер формы / размер')
//                        ->belowContent(fn ($value): ?string => Size::find($value)?->type)
//                        ->belowContent(fn (? $size): ?string => $size-> ?? '')
                        ->searchable(['number', 'size'])
                        ->reactive()
                        ->preload()
                        ->loadingMessage('Ищу по номерам и размерам...')
                        ->searchingMessage('Ищу по номерам и размерам...')
                        ->relationship(name: 'size', titleAttribute: 'number')
//                        ->options(
//                            Size::query()
//                                ->orderBy('number', 'ASC')
////                                ->limit(1000)
//                                ->pluck('number', 'id')
//                        )
//                            ->where('number', 'like', "%{$search}%")
//                            ->where('size', 'like', "%{$search}%")
//                            ->limit(50)
//                            Size::query()
//
//                                ->pluck('number', 'id')
//                            ->toArray()

                        ->searchPrompt('Начните вводить размер или номер')
                        ->noSearchResultsMessage('Ничего не нашлось')
                        ->required(),

                    Select::make('customer_id')
                        ->label('Клиент')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->required(),

                    Select::make('type_paper')
                        ->label('Тип бумаги')
                        ->options(Category::getChildrens('Тип бумаги')->pluck('name', 'id')->toArray()),

                    TextEntry::make('sale_1_page')
                        ->label('Стоимость 1 листа (база)')
                        ->dehydrated()
                        ->weight(FontWeight::Bold)
                        ->fontFamily(FontFamily::Mono)
                        ->state(fn (Get $get) => static::metric('sale_1_page', $get))
                        ->size(TextSize::Large),

                    Select::make('type_order')
                        ->label('Тип заказа')
                        ->reactive()
                        ->options([
                            'direct' => 'Прямой',//65
                            'agency' => 'Агенство',//75
                        ]),

                    TextEntry::make('sale_1_channel')
                        ->label('Стоимость 1м канала')
                        ->dehydrated()
                        ->weight(FontWeight::Bold)
                        ->fontFamily(FontFamily::Mono)
                        ->state(fn (Get $get) => static::metric('sale_1_channel', $get))
                        ->size(TextSize::Large),

                    CheckboxList::make('print_options')
                        ->label('Основное')
                        ->options([
                            'print' => 'Печать',
                            'lamination' => 'Ламинация',
                            'post_print' => 'Постпечатная обработка',
                            'handle'  => 'Ручка',
                            'luvers'  => 'Люверс',
                            //заливка - этап 2
                        ])
                        ->reactive(),
                ])
                ->columns()
                ->columnSpan(2),

            Section::make('Выберите нужное')
                ->schema([

//                    TextInput::make('colors')
//                        ->label('Цветность')
//                        ->numeric()
//                        ->default(0)
//                        ->disabled(fn(Get $get) => !in_array('print', $get('print_options'))),

                    //шелкография цветная, есть табличка 0+1 и тд
                    //офсет - камзол
                    Select::make('print_type')
                        ->label('Тип печати')
                        ->multiple()
                        ->reactive()
                        ->options(Category::getChildrens('Тип печати')->pluck('name', 'slug')->toArray())
                        ->disabled(fn(Get $get) => !in_array('print', (array) $get('print_options'), true)),

                    //офсет
                    Select::make('print_type_ofset')
                        ->label('Цветность офсет 1')
                        ->searchable()
                        ->reactive()
                        ->dehydrated()
                        ->options(Ofset::query()->pluck('colors', 'colors')->toArray())
                        ->visible(fn(Get $get) => in_array('ofset', (array) $get('print_type'), true)),

                    Select::make('print_type_ofset_2')
                        ->label('Цветность офсет 2')
                        ->searchable()
                        ->reactive()
                        ->dehydrated()
                        ->options(Ofset::query()->pluck('colors', 'colors')->toArray())
                        ->visible(fn(Get $get) => in_array('ofset', (array) $get('print_type'), true)),

//                    TextInput::make('print_type_ofset_count')
//                        ->label('Кол-во сторон офсет')
//                        ->integer()
//                        ->reactive()
//                        ->visible(fn(Get $get) => in_array('ofset', $get('print_type'))),

                    //шелкография
                    Select::make('print_type_selkografiia')
                        ->label('Цветность шелкография')
                        ->searchable()
                        ->reactive()
                        ->options(Ofset::query()->pluck('colors', 'colors')->toArray())
                        ->visible(fn(Get $get) => in_array('selkografiia', (array) $get('print_type'), true)),

//                    TextInput::make('print_type_selkografiia_count')
//                        ->label('Кол-во сторон шелкография')
//                        ->integer()
//                        ->reactive()
//                        ->visible(fn(Get $get) => in_array('selkografiia', $get('print_type'))),


                    //теснение, шелкография, УФ-лаком, хангнеф
                    Select::make('post_print_type')
                        ->label('Постпечатная обработка')
                        ->reactive()
                        ->multiple()
                        ->helperText(fn(Get $get) => $get('post_print_type') == 'uf-lak' ? '*Сушка тиража. Запечатка 20-50%' : null)
                        ->options(Category::getChildrens('Постпечатная обработка')->pluck('name', 'slug')->toArray())
                        ->disabled(fn(Get $get) => !in_array('post_print', (array) $get('print_options'), true)),

                    Select::make('type_lamination')
                        ->label('Тип ламинации')
                        ->reactive()
                        ->options(Category::getChildrens('Тип ламинации')->pluck('name', 'slug')->toArray())
                        ->disabled(fn(Get $get) => !in_array('lamination', (array) $get('print_options'), true)),

                    Checkbox::make('print_plashka')
                        ->label('Плашка')
                        ->reactive()
                        ->visible(fn(Get $get) => in_array('ofset', (array) $get('print_type'), true)),

                    Checkbox::make('print_option_discharge')
                        ->label('Коронирование')
                        ->reactive()
                        ->disabled(fn(Get $get) => !in_array('uf-lak', (array) $get('post_print_type'), true)),

                    Select::make('type_bracing_handle')
                        ->label('Тип крепления ручки')
                        ->reactive()
                        ->options(Category::getChildrens('Тип крепления ручки')->pluck('name', 'slug')->toArray())
                        ->disabled(fn(Get $get) => !in_array('handle', (array) $get('print_options'), true)),

                    Checkbox::make('handle_x2')
                        ->label('Ручка х2')
                        ->reactive()
                        ->visible(fn(Get $get) => !in_array('handle', (array) $get('print_options'), true)),

                    Select::make('type_handle')
                        ->label('Тип ручки')
                        ->reactive()
                        ->options(Category::getChildrens('Тип ручки')->pluck('name', 'id')->toArray())
                        ->disabled(fn(Get $get) => !in_array('handle', (array) $get('print_options'), true)),
                ])
                ->columns()
                ->columnSpan(2),

            //усложнение, шаг 2
//            Section::make('Пантоны')
//                ->schema([
//
//                    TextInput::make('panton_sale_1_weight')
//                        ->label('Сумма за 1 кг пантона')
//                        ->default(1)
//                        ->reactive()
//                        ->required(),
//
//                    TextEntry::make('panton_weight')
//                        ->label('Пантонов кг')
//                        ->state(fn (Get $get) => static::calcBase('panton_weight', $get))
//                        ->fontFamily(FontFamily::Mono)
//                        ->default(1)
//                        ->size(TextSize::Large)
//                        ->dehydrated(),
//                ]),
        ];
    }

//    public static function getOptionResultComponents(): array
//    {
//        return [];
//    }
//
//    public static function getResultComponents(): array
//    {
//        return [
//
//        ];
//    }

    private static function metric(string $key, Get $get): mixed
    {
        return static::calculate($get)->value($key);
    }

    private static function calculate(Get $get): \App\Domain\Orders\OrderCalculationResult
    {
        /** @var OrderCalculator $calculator */
        $calculator = app(OrderCalculator::class);

        return $calculator->calculate(OrderCalculationInput::fromGet($get));
    }
}
