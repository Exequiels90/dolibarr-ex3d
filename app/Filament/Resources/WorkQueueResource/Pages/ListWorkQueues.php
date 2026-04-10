<?php

namespace App\Filament\Resources\WorkQueueResource\Pages;

use App\Filament\Resources\WorkQueueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkQueues extends ListRecords
{
    protected static string $resource = WorkQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
