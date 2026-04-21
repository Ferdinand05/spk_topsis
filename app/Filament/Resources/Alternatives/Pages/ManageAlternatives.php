<?php

namespace App\Filament\Resources\Alternatives\Pages;

use App\Filament\Resources\Alternatives\AlternativeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAlternatives extends ManageRecords
{
    protected static string $resource = AlternativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
