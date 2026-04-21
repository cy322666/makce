<?php

namespace App\Filament\Clusters\Products\Resources\OfsetPrices;

use App\Filament\Clusters\Products\ProductsCluster;
use App\Filament\Clusters\Products\Resources\OfsetPrices\Pages\CreateOfsetPrice;
use App\Filament\Clusters\Products\Resources\OfsetPrices\Pages\EditOfsetPrice;
use App\Filament\Clusters\Products\Resources\OfsetPrices\Pages\ListOfsetPrices;
use App\Filament\Clusters\Products\Resources\OfsetPrices\Schemas\OfsetPriceForm;
use App\Filament\Clusters\Products\Resources\OfsetPrices\Tables\OfsetPricesTable;
use App\Models\Ofset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class OfsetPriceResource extends Resource
{
    protected static ?string $model = Ofset::class;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $recordTitleAttribute = 'colors';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-printer';

    protected static ?string $navigationParentItem = 'Products';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Прайс офсета';

    protected static ?string $modelLabel = 'Прайс офсета';

    public static function form(Schema $schema): Schema
    {
        return OfsetPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfsetPricesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfsetPrices::route('/'),
            'create' => CreateOfsetPrice::route('/create'),
            'edit' => EditOfsetPrice::route('/{record}/edit'),
        ];
    }
}
