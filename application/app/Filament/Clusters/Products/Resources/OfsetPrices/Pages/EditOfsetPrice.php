<?php

namespace App\Filament\Clusters\Products\Resources\OfsetPrices\Pages;

use App\Filament\Clusters\Products\Resources\OfsetPrices\OfsetPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOfsetPrice extends EditRecord
{
    protected static string $resource = OfsetPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
