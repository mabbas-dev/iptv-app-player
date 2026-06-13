<?php

namespace App\Filament\Reseller\Resources\ApiKeyResource\Pages;

use App\Filament\Reseller\Resources\ApiKeyResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateApiKey extends CreateRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reseller_id'] = Filament::auth()->id();

        return $data;
    }
}
