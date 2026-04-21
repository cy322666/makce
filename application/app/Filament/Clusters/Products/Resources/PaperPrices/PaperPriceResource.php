<?php

namespace App\Filament\Clusters\Products\Resources\PaperPrices;

use App\Filament\Clusters\Products\ProductsCluster;
use App\Filament\Clusters\Products\Resources\PaperPrices\Pages\CreatePaperPrice;
use App\Filament\Clusters\Products\Resources\PaperPrices\Pages\EditPaperPrice;
use App\Filament\Clusters\Products\Resources\PaperPrices\Pages\ListPaperPrices;
use App\Filament\Clusters\Products\Resources\PaperPrices\Schemas\PaperPriceForm;
use App\Filament\Clusters\Products\Resources\PaperPrices\Tables\PaperPricesTable;
use App\Models\PaperPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PaperPriceResource extends Resource
{
    protected static ?string $model = PaperPrice::class;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationParentItem = 'Products';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Прайс бумаги';

    protected static ?string $modelLabel = 'Прайс бумаги';

    public static function form(Schema $schema): Schema
    {
        return PaperPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaperPricesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaperPrices::route('/'),
            'create' => CreatePaperPrice::route('/create'),
            'edit' => EditPaperPrice::route('/{record}/edit'),
        ];
    }
}
