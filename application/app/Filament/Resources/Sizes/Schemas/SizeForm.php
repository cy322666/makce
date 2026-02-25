<?php

namespace App\Filament\Resources\Sizes\Schemas;

use App\Models\Order;
use App\Models\Size;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SizeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema(static::getDetailsComponents())
                            ->columns(2),

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
                    ])
                    ->columnSpan(['lg' => fn (?Size $record) => $record === null ? 3 : 2]),

                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Order date')
                            ->state(fn (Size $record): ?string => $record->created_at?->diffForHumans()),

                        TextEntry::make('updated_at')
                            ->label('Last modified at')
                            ->state(fn (Size $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Size $record) => $record === null),
            ])
            ->columns(3);
    }

    /**
     * @throws \Exception
     */
    public static function getDetailsComponents(): array
    {
        return [
            Select::make('type')
                ->label('Размер листа')
                ->options(Size::query()->pluck('type', 'type')),

            TextInput::make('number')
                ->label('Номер формы')
                ->unique(),

//            TextInput::make('size_1')
//                ->label('Ш'),
//            TextInput::make('size_2')
//                ->label('Г'),
//            TextInput::make('size_3')
//                ->label('В'),
            TextInput::make('count_1')
                ->label('Пакетов на А1'),
            TextInput::make('count_blank')
                ->label('Заготовок на пакет'),
            TextInput::make('size')
                ->label('Размер'),
            TextInput::make('size_blank')
                ->label('Размер заготовки'),
            TextInput::make('size_paper')
                ->label('Размер бумаги'),
            TextInput::make('number')
                ->label('Номер'),
            TextInput::make('package')
                ->label(''),
            TextInput::make('membrane')
                ->label(''),

//            ToggleButtons::make('status')
//                ->inline()
//                ->options(OrderStatus::class)
//                ->required(),

//            AddressForm::make('address')
//                ->columnSpan('full'),
//
//            RichEditor::make('notes')
//                ->columnSpan('full'),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship()
            ->table([
                TableColumn::make('Product'),
                TableColumn::make('Quantity')
                    ->width(100),
                TableColumn::make('Unit Price')
                    ->width(110),
            ])
            ->schema([
                Select::make('shop_product_id')
                    ->label('Product')
//                    ->options(Product::query()->pluck('name', 'id'))
                    ->options([])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, Set $set) => $set('unit_price', Product::find($state)->price ?? 0))
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->searchable(),

                TextInput::make('qty')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->required(),

                TextInput::make('unit_price')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required(),
            ])
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip('Open product')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
//                        $itemData = $component->getRawItemState($arguments['item']);

                        $product = false;
//                        $product = Product::find($itemData['shop_product_id']);

                        if (! $product) {
                            return null;
                        }

//                        return ProductResource::getUrl('edit', ['record' => $product]);
                        return '';
                    }, shouldOpenInNewTab: true)
//                    ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['shop_product_id'])),
            ])
            ->orderColumn('sort')
            ->defaultItems(1)
            ->hiddenLabel()
            ->required();
    }
}
