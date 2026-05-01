<?php

namespace App\Filament\Resources\Sizes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SizesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Размер пакета')
//                    ->label('Last modified at')
//                    ->date()
                    ->sortable(),
//                TextColumn::make('size_1')
//                    ->label('Ш'),
//                TextColumn::make('size_2')
//                    ->label('Г'),
//                TextColumn::make('size_3')
//                    ->label('В'),
                TextColumn::make('count_1')
                    ->label('Пакетов на А1'),
                TextColumn::make('count_blank')
                    ->label('Заготовок на пакет'),
                TextColumn::make('size')
                    ->label('Размер'),
                TextColumn::make('size_blank')
                    ->label('Размер заготовки'),
                TextColumn::make('size_paper')
                    ->label('Размер бумаги'),
                TextColumn::make('paper_format')
                    ->label('Формат бумаги')
                    ->sortable(),
                TextColumn::make('number')
                    ->label('Номер'),
                TextColumn::make('package')
                    ->label('Упаковка')
                    ->sortable(),
                TextColumn::make('membrane')
                    ->label('Пленка'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
