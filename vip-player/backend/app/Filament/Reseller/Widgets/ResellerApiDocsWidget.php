<?php

namespace App\Filament\Reseller\Widgets;

use Filament\Widgets\Widget;

class ResellerApiDocsWidget extends Widget
{
    protected static string $view = 'filament.reseller.widgets.reseller-api-docs';

    protected int | string | array $columnSpan = 'full';
}
