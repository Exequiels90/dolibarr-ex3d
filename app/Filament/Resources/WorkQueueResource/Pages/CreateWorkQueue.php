<?php

namespace App\Filament\Resources\WorkQueueResource\Pages;

use App\Filament\Resources\WorkQueueResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkQueue extends CreateRecord
{
    protected static string $resource = WorkQueueResource::class;
}
