<?php

namespace App\Filament\Clusters\Products\Resources\PaperPrices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaperPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Прайс бумаги')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('match_key')
                                    ->label('Ключ для калькулятора')
                                    ->helperText('Например: bumaga-mel-170g-72104')
                                    ->required(),

                                TextInput::make('title')
                                    ->label('Название')
                                    ->required(),

                                TextInput::make('group_name')
                                    ->label('Группа')
                                    ->nullable(),

                                TextInput::make('sheet_format')
                                    ->label('Формат листа')
                                    ->required(),

                                TextInput::make('base_price')
                                    ->label('База')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('markup_percent')
                                    ->label('Наценка %')
                                    ->numeric()
                                    ->default(10)
                                    ->required(),

                                TextInput::make('sale_price')
                                    ->label('Цена листа')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('note')
                                    ->label('Комментарий')
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }
}
