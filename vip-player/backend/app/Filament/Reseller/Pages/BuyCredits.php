<?php

namespace App\Filament\Reseller\Pages;

use App\Models\AppSetting;
use App\Support\StripeHelper;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class BuyCredits extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Buy Credits';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.reseller.pages.buy-credits';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['credits' => 10]);

        if (request()->query('status') === 'success') {
            Notification::make()->success()
                ->title('Payment successful')
                ->body('Credits have been added to your wallet.')
                ->send();
        } elseif (request()->query('status') === 'cancelled') {
            Notification::make()->warning()
                ->title('Payment cancelled')
                ->send();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('credits')
                    ->label('Number of credits')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(10000)
                    ->suffix('credits')
                    ->helperText('1 credit = 1 month device activation. Price per credit: $' . $this->getCreditPrice()),
            ])
            ->statePath('data');
    }

    public function getCreditPrice(): string
    {
        return number_format((float) AppSetting::get('credit_price_usd', '1.00'), 2);
    }

    public function checkout(): mixed
    {
        if (! StripeHelper::isEnabled()) {
            Notification::make()->warning()
                ->title('Payments disabled')
                ->body('Online credit purchases are currently disabled. Contact admin for manual top-up.')
                ->send();

            return null;
        }

        $data = $this->form->getState();
        $credits = (int) $data['credits'];
        $priceUsd = (float) AppSetting::get('credit_price_usd', '1.00');
        $reseller = Filament::auth()->user();

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'mode' => 'payment',
            'customer_email' => $reseller->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => (int) round($priceUsd * 100),
                    'product_data' => [
                        'name' => 'FOX PLAYER Credits',
                        'description' => '1 credit = 1 month device activation',
                    ],
                ],
                'quantity' => $credits,
            ]],
            'metadata' => [
                'reseller_id' => $reseller->id,
                'credits' => $credits,
            ],
            'success_url' => route('reseller.credits.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/reseller/buy-credits?status=cancelled'),
        ]);

        return redirect()->away($session->url);
    }
}
