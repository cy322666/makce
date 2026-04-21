<?php

namespace App\Filament\Clusters\Products\Resources\PaperPrices\Pages;

use App\Filament\Clusters\Products\Resources\PaperPrices\PaperPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaperPrice extends EditRecord
{
    protected static string $resource = PaperPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
