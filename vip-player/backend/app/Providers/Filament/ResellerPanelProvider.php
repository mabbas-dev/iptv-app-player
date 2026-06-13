<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ResellerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('reseller')
            ->path('reseller')
            ->login()
            ->registration(\App\Filament\Reseller\Pages\Auth\Register::class)
            ->authGuard('reseller')
            ->brandName('FOX PLAYER Reseller')
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Zinc,
            ])
            ->darkMode(true)
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Reseller/Resources'), for: 'App\\Filament\\Reseller\\Resources')
            ->discoverPages(in: app_path('Filament/Reseller/Pages'), for: 'App\\Filament\\Reseller\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Reseller/Widgets'), for: 'App\\Filament\\Reseller\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
