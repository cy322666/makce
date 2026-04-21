<?php

namespace App\Filament\Clusters\Products\Resources\OfsetPrices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OfsetPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Прайс офсета')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('colors')
                                    ->label('Цветность')
                                    ->required(),

                                TextInput::make('sale_preparation')
                                    ->label('Приладка')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('sale_print')
                                    ->label('Печать')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('sale_print_mel_paper')
                                    ->label('Печать на мелованной бумаге')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('circulation_100')
                                    ->label('100')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('circulation_300')
                                    ->label('300')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('circulation_500')
                                    ->label('500')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('circulation_1000')
                                    ->label('1000')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('circulation_2000')
                                    ->label('2000')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('circulation_3000')
                                    ->label('3000')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('circulation_5000')
                                    ->label('5000')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('circulation_10000')
                                    ->label('10000')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('circulation_15000')
                                    ->label('15000')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('circulation_20000')
                                    ->label('20000')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('circulation_50000')
                                    ->label('50000')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('circulation_100000')
                                    ->label('100000')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('circulation_500000')
                                    ->label('500000')
                                    ->numeric()
                                    ->nullable(),

                                TextInput::make('circulation_1000000')
                                    ->label('1000000')
                                    ->numeric()
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }
}
