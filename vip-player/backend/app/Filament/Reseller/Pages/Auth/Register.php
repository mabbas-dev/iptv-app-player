<?php

namespace App\Filament\Reseller\Pages\Auth;

use App\Models\Reseller;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form->schema([
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            TextInput::make('phone')
                ->tel()
                ->maxLength(30),
            TextInput::make('company_name')
                ->label('Company / Shop name')
                ->maxLength(255),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['status'] = Reseller::STATUS_ACTIVE;

        return $data;
    }
}
