<?php

namespace App\Filament\Clusters\Products\Resources\Groups\Schemas;

use App\Filament\Clusters\Products\Resources\Groups\Tables\GroupsTable;
use App\Models\Category;
use App\Models\Group;
use App\Models\Size;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->schema([

                                TextInput::make('number_id')
                                    ->label('Номер формы')
                                    ->required(),

                                TextInput::make('group_name')
                                    ->label('Название группы')
//                                    ->options([
//                                        'А1',
//                                        'А3,А2',
//                                        'А4,А5'
//                                    ])
                                    ->required(),

                                TextInput::make('bottom')
                                    ->numeric('2')
                                    ->label('Дно'),
                                TextInput::make('handle_1')
                                    ->numeric('2')
                                    ->label('Рукав 1'),
                                TextInput::make('handle_2')
                                    ->numeric('2')
                                    ->label('Рукав 2'),
                                TextInput::make('luvers')
                                    ->numeric('2')
                                    ->label('Люверс'),
                                TextInput::make('cutting_cord_2')
                                    ->numeric('2')
                                    ->label('Резка шнура 2шт'),
                                TextInput::make('sidewall')
                                    ->numeric('2')
                                    ->label('Вставка боковины'),
                                TextInput::make('boking_gluing')
                                    ->numeric('2')
                                    ->label('Склейка боковины'),
                                TextInput::make('hole')
                                    ->numeric('2')
                                    ->label('Дырка'),
//                                    ->live(onBlur: true)
//                                    ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

//                                TextInput::make('slug')
//                                    ->disabled()
//                                    ->dehydrated()
//                                    ->required()
//                                    ->maxLength(255)
//                                    ->unique(Category::class, 'slug', ignoreRecord: true),
                            ]),

//                        Select::make('parent_id')
//                            ->relationship('parent', 'name', fn (Builder $query) => $query->where('parent_id', null))
//                            ->searchable()
//                            ->placeholder('Select parent category'),

//                        Select::make('category_name')
//                            ->relationship('parent', 'name', fn (Builder $query) => $query->where('parent_id', null))
//                            ->searchable()
//                            ->placeholder('Select parent category'),

//                        Toggle::make('is_visible')
//                            ->label('Visibility')
//                            ->default(true),
//
//                        RichEditor::make('description'),
                    ])
                    ->columnSpan(['lg' => fn (?Group $record) => $record === null ? 3 : 2]),
                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->state(fn (Group $record): ?string => $record->created_at?->diffForHumans()),

                        TextEntry::make('updated_at')
                            ->label('Last modified at')
                            ->state(fn (Group $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Group $record) => $record === null),
            ])
            ->columns(3);
    }
}
