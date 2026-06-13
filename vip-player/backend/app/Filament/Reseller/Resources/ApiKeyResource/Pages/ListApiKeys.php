<?php

namespace App\Filament\Reseller\Resources\ApiKeyResource\Pages;

use App\Filament\Reseller\Resources\ApiKeyResource;
use App\Filament\Reseller\Widgets\ResellerApiDocsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApiKeys extends ListRecords
{
    protected static string $resource = ApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ResellerApiDocsWidget::class,
        ];
    }
}
