<?php

namespace App\Filament\Clusters\Products\Resources\Categories\Pages;

use App\Filament\Clusters\Products\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

//use App\Filament\Imports\CategoryImporter;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getActions(): array
    {
        return [
//            ImportAction::make()
//                ->importer(CategoryImporter::class),
            CreateAction::make(),
        ];
    }
}
