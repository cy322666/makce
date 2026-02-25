<?php

namespace App\Filament\Clusters\Products\Resources\Groups\Pages;

use App\Filament\Clusters\Products\Resources\Categories\CategoryResource;
//use App\Filament\Imports\CategoryImporter;
use App\Filament\Clusters\Products\Resources\Groups\GroupResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListGroups extends ListRecords
{
    protected static string $resource = GroupResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
