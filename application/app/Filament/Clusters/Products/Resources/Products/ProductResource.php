<?php

namespace App\Filament\Clusters\Products\Resources\Products;

use App\Filament\Clusters\Products\ProductsCluster;
use App\Filament\Clusters\Products\Resources\Products\Pages\CreateProduct;
use App\Filament\Clusters\Products\Resources\Products\Pages\EditProduct;
use App\Filament\Clusters\Products\Resources\Products\Pages\ListProducts;
use App\Filament\Clusters\Products\Resources\Products\RelationManagers\CommentsRelationManager;
use App\Filament\Clusters\Products\Resources\Products\Schemas\ProductForm;
use App\Filament\Clusters\Products\Resources\Products\Tables\ProductsTable;
use App\Filament\Clusters\Products\Resources\Products\Widgets\ProductStats;
use App\Models\Product;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Цены материалы/работа';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'Цены';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

//    public static function getRelations(): array
//    {
//        return [
//            CommentsRelationManager::class,
//        ];
//    }

//    public static function getWidgets(): array
//    {
//        return [
//            ProductStats::class,
//        ];
//    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'category.name'];
    }

//    public static function getGlobalSearchResultDetails(Model $record): array
//    {
//        /** @var Product $record */
//
//        return [
//            'Brand' => optional($record->brand)->name,
//        ];
//    }

    /** @return Builder<Product> */
//    public static function getGlobalSearchEloquentQuery(): Builder
//    {
//        return parent::getGlobalSearchEloquentQuery()->with(['brand']);
//    }

//    public static function getNavigationBadge(): ?string
//    {
//        /** @var class-string<Model> $modelClass */
//        $modelClass = static::$model;
//
//        return 0;
//    }
}
