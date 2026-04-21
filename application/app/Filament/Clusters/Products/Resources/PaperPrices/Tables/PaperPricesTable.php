<?php

namespace App\Filament\Clusters\Products\Resources\PaperPrices\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaperPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationPageOptions([50, 100, 200])
            ->defaultPaginationPageOption(50)
            ->defaultSort('sheet_format')
            ->columns([
                TextColumn::make('title')
                    ->label('Название')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('match_key')
                    ->label('Ключ')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('group_name')
                    ->label('Группа')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('sheet_format')
                    ->label('Формат')
                    ->sortable(),

                TextColumn::make('base_price')
                    ->label('База')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state === null ? '-' : number_format((float) $state, 2, '.', ' ')),

                TextColumn::make('markup_percent')
                    ->label('Наценка %')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ' ')),

                TextColumn::make('sale_price')
                    ->label('Цена листа')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state === null ? '-' : number_format((float) $state, 3, '.', ' ')),

                TextColumn::make('note')
                    ->label('Комментарий')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

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
