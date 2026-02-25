<?php

namespace App\Filament\Clusters\Products\Resources\Groups;

use App\Filament\Clusters\Products\ProductsCluster;
use App\Filament\Clusters\Products\Resources\Groups\Pages\CreateGroup;
use App\Filament\Clusters\Products\Resources\Groups\Pages\EditGroup;
use App\Filament\Clusters\Products\Resources\Groups\Pages\ListGroups;
//use App\Filament\Clusters\Products\Resources\Groups\RelationManagers\ProductsRelationManager;
use App\Filament\Clusters\Products\Resources\Groups\Schemas\GroupForm;
use App\Filament\Clusters\Products\Resources\Groups\Tables\GroupsTable;
use App\Models\Category;
use App\Models\Group;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationParentItem = 'Products';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Группы (ПП)';

    public static function form(Schema $schema): Schema
    {
        return GroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
//            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroups::route('/'),
            'create' => CreateGroup::route('/create'),
            'edit' => EditGroup::route('/{record}/edit'),
        ];
    }
}
