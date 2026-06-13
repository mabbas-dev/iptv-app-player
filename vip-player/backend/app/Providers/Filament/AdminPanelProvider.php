<?php

namespace App\Providers\Filament;

use App\Models\AppSetting;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $adminPath = $this->adminPath();

        return $panel
            ->default()
            ->id('admin')
            ->path($adminPath)
            ->login()
            ->brandName('FOX PLAYER')
            ->favicon(null)
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Zinc,
            ])
            ->darkMode(true)
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->navigationGroups([
                'Devices & Subscriptions',
                'Resellers & Credits',
                'Customer Plans',
                'Support & Settings',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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

    protected function adminPath(): string
    {
        if ($path = env('ADMIN_PATH')) {
            return trim($path, '/');
        }

        try {
            return trim(AppSetting::get('admin_panel_path', 'vip-panel-secure'), '/');
        } catch (\Throwable) {
            return 'vip-panel-secure';
        }
    }
}
