<?php

namespace App\Filament\Clusters\Products\Resources\OfsetPrices\Pages;

use App\Filament\Clusters\Products\Resources\OfsetPrices\OfsetPriceResource;
use Filament\Resources\Pages\ListRecords;

class ListOfsetPrices extends ListRecords
{
    protected static string $resource = OfsetPriceResource::class;
}
