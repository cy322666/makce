<?php

namespace App\Filament\Clusters\Products\Resources\PaperPrices\Pages;

use App\Filament\Clusters\Products\Resources\PaperPrices\PaperPriceResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaperPrice extends CreateRecord
{
    protected static string $resource = PaperPriceResource::class;
}
