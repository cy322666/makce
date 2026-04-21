<?php

namespace App\Filament\Clusters\Products\Resources\UvLacPrices\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UvLacPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationPageOptions([50, 100, 200])
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('format')
                    ->label('Формат')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('process_type')
                    ->label('Процесс')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('lacquer_type')
                    ->label('Лак')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('min_run')
                    ->label('От листов')
                    ->sortable(),

                TextColumn::make('max_run')
                    ->label('До листов')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? '∞'),

                TextColumn::make('price')
                    ->label('Цена')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ' ')),

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
