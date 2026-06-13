<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    protected static ?string $navigationGroup = 'Support & Settings';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.manage-site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'stripe_enabled' => AppSetting::get('stripe_enabled', '1') === '1',
            'site_url' => AppSetting::get('site_url', 'https://foxplayer.app'),
            'apk_download_url' => AppSetting::get('apk_download_url', 'https://foxplayer.app/download/app'),
            'support_email' => AppSetting::get('support_email', 'support@foxplayer.app'),
            'support_whatsapp' => AppSetting::get('support_whatsapp', ''),
            'support_message' => AppSetting::get('support_message', ''),
            'legal_disclaimer' => AppSetting::get('legal_disclaimer', ''),
            'terms_of_service' => AppSetting::get('terms_of_service', ''),
            'privacy_policy' => AppSetting::get('privacy_policy', ''),
            'refund_policy' => AppSetting::get('refund_policy', ''),
            'activation_policy' => AppSetting::get('activation_policy', ''),
            'acceptable_use_policy' => AppSetting::get('acceptable_use_policy', ''),
            'cookie_policy' => AppSetting::get('cookie_policy', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Payments')
                ->description('Control Stripe checkout on the public activation page and reseller credit purchases.')
                ->schema([
                    Toggle::make('stripe_enabled')
                        ->label('Enable Stripe payments')
                        ->helperText('When disabled, direct purchase is hidden. Users are directed to contact support or official resellers.'),
                ]),
            Section::make('Website')
                ->schema([
                    TextInput::make('site_url')
                        ->label('Public website URL')
                        ->placeholder('https://foxplayer.app')
                        ->helperText('Used for QR codes and upload links in the app.'),
                    TextInput::make('apk_download_url')
                        ->label('APK download URL')
                        ->placeholder('https://foxplayer.app/download/app')
                        ->helperText('Direct download link shown on the homepage.'),
                ]),
            Section::make('Support Contact')
                ->columns(2)
                ->schema([
                    TextInput::make('support_email')->email(),
                    TextInput::make('support_whatsapp')->label('WhatsApp number'),
                    Textarea::make('support_message')->columnSpanFull()->rows(2),
                ]),
            Section::make('Legal Pages')
                ->description('Content shown on public legal pages. HTML is not required — plain text with line breaks is supported.')
                ->collapsed()
                ->schema([
                    Textarea::make('legal_disclaimer')->rows(3)->columnSpanFull(),
                    Textarea::make('terms_of_service')->label('Terms & Conditions')->rows(8)->columnSpanFull(),
                    Textarea::make('privacy_policy')->label('Privacy Policy')->rows(8)->columnSpanFull(),
                    Textarea::make('refund_policy')->label('Refund Policy')->rows(6)->columnSpanFull(),
                    Textarea::make('activation_policy')->label('Activation Policy')->rows(6)->columnSpanFull(),
                    Textarea::make('acceptable_use_policy')->label('Acceptable Use Policy')->rows(6)->columnSpanFull(),
                    Textarea::make('cookie_policy')->label('Cookie Policy')->rows(4)->columnSpanFull(),
                ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AppSetting::set('stripe_enabled', $data['stripe_enabled'] ? '1' : '0');
        AppSetting::set('site_url', rtrim($data['site_url'] ?? 'https://foxplayer.app', '/'));
        AppSetting::set('apk_download_url', $data['apk_download_url'] ?? 'https://foxplayer.app/download/app');
        AppSetting::set('support_email', $data['support_email'] ?? '');
        AppSetting::set('support_whatsapp', $data['support_whatsapp'] ?? '');
        AppSetting::set('support_message', $data['support_message'] ?? '');
        AppSetting::set('legal_disclaimer', $data['legal_disclaimer'] ?? '');
        AppSetting::set('terms_of_service', $data['terms_of_service'] ?? '');
        AppSetting::set('privacy_policy', $data['privacy_policy'] ?? '');
        AppSetting::set('refund_policy', $data['refund_policy'] ?? '');
        AppSetting::set('activation_policy', $data['activation_policy'] ?? '');
        AppSetting::set('acceptable_use_policy', $data['acceptable_use_policy'] ?? '');
        AppSetting::set('cookie_policy', $data['cookie_policy'] ?? '');

        Notification::make()->success()->title('Settings saved')->send();
    }
}
