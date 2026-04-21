<?php

namespace App\Filament\Clusters\Products\Resources\UvLacPrices\Schemas;

use App\Models\LacPrice;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UvLacPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Параметры прайса')
                            ->schema([
                                Select::make('format')
                                    ->label('Формат')
                                    ->options([
                                        'A3' => 'A3',
                                        'A2' => 'A2',
                                        'A1' => 'A1',
                                    ])
                                    ->required(),

                                Select::make('process_type')
                                    ->label('Тип процесса')
                                    ->options([
                                        'Сплошное' => 'Сплошное',
                                        'Выборочное' => 'Выборочное',
                                    ])
                                    ->required(),

                                Select::make('lacquer_type')
                                    ->label('Тип лака')
                                    ->options([
                                        'Матовая' => 'Матовая',
                                        'Глянцевая' => 'Глянцевая',
                                        'Софт-тач' => 'Софт-тач',
                                    ])
                                    ->required(),

                                TextInput::make('min_run')
                                    ->label('От листов')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('max_run')
                                    ->label('До листов')
                                    ->numeric()
                                    ->nullable()
                                    ->helperText('Если поле пустое, диапазон считается без верхней границы'),

                                TextInput::make('price')
                                    ->label('Цена за лист')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),
            ]);
    }
}
