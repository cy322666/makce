<?php

namespace App\Filament\Clusters\Products\Resources\UvLacPrices\Pages;

use App\Filament\Clusters\Products\Resources\UvLacPrices\UvLacPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUvLacPrice extends EditRecord
{
    protected static string $resource = UvLacPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
