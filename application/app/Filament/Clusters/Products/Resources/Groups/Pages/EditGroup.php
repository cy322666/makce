<?php

namespace App\Filament\Clusters\Products\Resources\Groups\Pages;

use App\Filament\Clusters\Products\Resources\Groups\GroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
