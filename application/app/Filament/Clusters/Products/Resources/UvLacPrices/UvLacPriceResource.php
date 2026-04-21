<?php

namespace App\Filament\Clusters\Products\Resources\UvLacPrices;

use App\Filament\Clusters\Products\ProductsCluster;
use App\Filament\Clusters\Products\Resources\UvLacPrices\Pages\CreateUvLacPrice;
use App\Filament\Clusters\Products\Resources\UvLacPrices\Pages\EditUvLacPrice;
use App\Filament\Clusters\Products\Resources\UvLacPrices\Pages\ListUvLacPrices;
use App\Filament\Clusters\Products\Resources\UvLacPrices\Schemas\UvLacPriceForm;
use App\Filament\Clusters\Products\Resources\UvLacPrices\Tables\UvLacPricesTable;
use App\Models\LacPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UvLacPriceResource extends Resource
{
    protected static ?string $model = LacPrice::class;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $recordTitleAttribute = 'format';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationParentItem = 'Products';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Прайс УФ-лака';

    protected static ?string $modelLabel = 'Прайс УФ-лака';

    public static function form(Schema $schema): Schema
    {
        return UvLacPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UvLacPricesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUvLacPrices::route('/'),
            'create' => CreateUvLacPrice::route('/create'),
            'edit' => EditUvLacPrice::route('/{record}/edit'),
        ];
    }
}
