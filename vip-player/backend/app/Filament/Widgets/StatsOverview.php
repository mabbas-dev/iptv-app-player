<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\Reseller;
use App\Models\SupportTicket;
use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Devices', Device::count())
                ->description(Device::where('status', Device::STATUS_ACTIVE)->count() . ' active · ' . Device::where('status', Device::STATUS_TRIAL)->count() . ' on trial')
                ->descriptionIcon('heroicon-m-device-tablet')
                ->color('success'),
            Stat::make('Resellers', Reseller::count())
                ->description(Reseller::where('status', Reseller::STATUS_ACTIVE)->count() . ' active')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Credits in Circulation', Wallet::sum('balance'))
                ->description('Total reseller wallet balance')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
            Stat::make('Open Tickets', SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count())
                ->description('Awaiting support reply')
                ->descriptionIcon('heroicon-m-lifebuoy')
                ->color('danger'),
        ];
    }
}
