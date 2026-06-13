<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateDevice extends CreateRecord
{
    protected static string $resource = DeviceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $pin = $this->form->getRawState()['new_parental_pin'] ?? null;
        if ($pin) {
            $data['parental_pin_hash'] = Hash::make($pin);
        }

        return $data;
    }
}
