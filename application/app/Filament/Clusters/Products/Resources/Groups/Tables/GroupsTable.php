<?php

namespace App\Filament\Clusters\Products\Resources\Groups\Tables;

use App\Models\Group;
use App\Models\Size;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupsTable
{
    static array $ignoreFields = [
        'id',
        'created_at',
        'updated_at',
        'number_id',
        'group_name',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number_id')
                    ->label('Номер формы')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group_name')
                    ->label('Название группы')
                    ->sortable(),

                TextColumn::make('bottom')
                    ->label('Дно'),
                TextColumn::make('handle_1')
                    ->label('Рукав 1'),
                TextColumn::make('handle_2')
                    ->label('Рукав 2'),
                TextColumn::make('luvers')
                    ->label('Люверс'),
                TextColumn::make('cutting_cord_2')
                    ->label('Резка шнура 2шт'),
                TextColumn::make('sidewall')
                    ->label('Вставка боковины'),
                TextColumn::make('boking_gluing')
                    ->label('Склейка боковины'),
                TextColumn::make('hole')
                    ->label('Дырка'),

                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function (): void {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
