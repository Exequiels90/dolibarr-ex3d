<?php

namespace App\Filament\Resources\AdditionalSupplyResource\Pages;

use App\Filament\Resources\AdditionalSupplyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdditionalSupplies extends ListRecords
{
    protected static string $resource = AdditionalSupplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
