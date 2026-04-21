<?php

namespace App\Filament\Clusters\Products\Resources\OfsetPrices\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfsetPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationPageOptions([50, 100, 200])
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('colors')
                    ->label('Цветность')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('sale_preparation')
                    ->label('Приладка')
                    ->sortable(),

                TextColumn::make('sale_print')
                    ->label('Печать')
                    ->sortable(),

                TextColumn::make('sale_print_mel_paper')
                    ->label('Печать на мелованной бумаге')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('circulation_100')
                    ->label('100')
                    ->sortable(),

                TextColumn::make('circulation_300')
                    ->label('300')
                    ->sortable(),

                TextColumn::make('circulation_500')
                    ->label('500')
                    ->sortable(),

                TextColumn::make('circulation_1000')
                    ->label('1000')
                    ->sortable(),

                TextColumn::make('circulation_2000')
                    ->label('2000')
                    ->sortable(),

                TextColumn::make('circulation_3000')
                    ->label('3000')
                    ->sortable(),

                TextColumn::make('circulation_5000')
                    ->label('5000')
                    ->sortable(),

                TextColumn::make('circulation_10000')
                    ->label('10000')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
