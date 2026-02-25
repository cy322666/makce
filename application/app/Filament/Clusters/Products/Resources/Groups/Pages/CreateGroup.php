<?php

namespace App\Filament\Clusters\Products\Resources\Groups\Pages;

use App\Filament\Clusters\Products\Resources\Categories\CategoryResource;
use App\Filament\Clusters\Products\Resources\Groups\GroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;
}
