<?php

namespace App\Filament\Reseller\Widgets;

use App\Models\Device;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WalletStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        /** @var \App\Models\Reseller $reseller */
        $reseller = Filament::auth()->user();

        return [
            Stat::make('Credit Balance', $reseller->wallet?->balance ?? 0)
                ->description('1 credit = 1 month activation')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
            Stat::make('My Devices', $reseller->devices()->count())
                ->description($reseller->devices()->where('status', Device::STATUS_ACTIVE)->count() . ' active')
                ->descriptionIcon('heroicon-m-device-tablet')
                ->color('success'),
            Stat::make('API Keys', $reseller->apiKeys()->where('is_active', true)->count())
                ->description('For bot & automation')
                ->descriptionIcon('heroicon-m-key')
                ->color('info'),
        ];
    }
}
