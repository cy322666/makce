<?php

namespace App\Filament\Clusters\Products\Resources\PaperPrices\Pages;

use App\Filament\Clusters\Products\Resources\PaperPrices\PaperPriceResource;
use Filament\Resources\Pages\ListRecords;

class ListPaperPrices extends ListRecords
{
    protected static string $resource = PaperPriceResource::class;
}
