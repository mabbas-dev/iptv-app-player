<?php

namespace App\Filament\Reseller\Resources;

use App\Filament\Reseller\Resources\DeviceResource\Pages;
use App\Models\Device;
use App\Models\Plan;
use App\Services\DeviceActivationService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-tablet';

    protected static ?string $navigationLabel = 'My Devices';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('reseller_id', Filament::auth()->id())
            ->whereDoesntHave('subscriptions', function (Builder $query) {
                $query->whereNotNull('stripe_payment_id')->whereNull('reseller_id');
            });
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device_code')
                    ->label('Device ID')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'android_tv' ? 'Android TV' : 'Android'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Device::STATUS_ACTIVE => 'success',
                        Device::STATUS_TRIAL => 'warning',
                        Device::STATUS_EXPIRED, Device::STATUS_BLOCKED => 'danger',
                        Device::STATUS_SUSPENDED => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subscription_ends_at')
                    ->label('Expires')
                    ->formatStateUsing(fn (Device $record) => $record->is_lifetime
                        ? 'Lifetime'
                        : ($record->effectiveExpiry()?->format('M j, Y') ?? '—'))
                    ->placeholder('—')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw(
                            'GREATEST(COALESCE(subscription_ends_at, 0), COALESCE(trial_ends_at, 0)) '.$direction
                        );
                    }),
                Tables\Columns\TextColumn::make('last_seen_at')->label('Last seen')->since()->placeholder('Never'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(array_combine(Device::STATUSES, array_map('ucfirst', Device::STATUSES))),
            ])
            ->headerActions([
                Tables\Actions\Action::make('claim_device')
                    ->label('Add Device')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\TextInput::make('device_code')
                            ->label('Device ID (shown in the app)')
                            ->placeholder('A1:B2:C3:D4:E5:F6')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $device = Device::where('device_code', strtoupper(trim($data['device_code'])))->first();

                        if (! $device) {
                            Notification::make()->danger()->title('Device not found')
                                ->body('Ask your customer to open the FOX PLAYER app and read the Device ID from the activation screen.')
                                ->send();

                            return;
                        }

                        if ($device->hasDirectCustomerPurchase()) {
                            Notification::make()->danger()
                                ->title('Device has a direct website subscription')
                                ->body('This customer purchased from the website. It is not managed by resellers.')
                                ->send();

                            return;
                        }

                        if ($device->reseller_id && $device->reseller_id !== Filament::auth()->id()) {
                            Notification::make()->danger()->title('Device already belongs to another reseller')->send();

                            return;
                        }

                        $device->update(['reseller_id' => Filament::auth()->id()]);
                        Notification::make()->success()->title('Device added to your account')->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->label('Activate / Renew')
                    ->icon('heroicon-o-bolt')
                    ->color('success')
                    ->visible(fn (Device $record) => ! $record->hasDirectCustomerPurchase())
                    ->form([
                        Forms\Components\Select::make('plan_id')
                            ->label('Plan')
                            ->options(
                                Plan::where('is_active', true)
                                    ->where('plan_type', 'reseller')
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->mapWithKeys(fn (Plan $plan) => [
                                        $plan->id => "{$plan->name} — {$plan->credit_cost} credits",
                                    ])
                            )
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (Device $record, array $data, DeviceActivationService $service) {
                        if ($record->hasDirectCustomerPurchase()) {
                            Notification::make()->danger()
                                ->title('Cannot renew')
                                ->body('This customer purchased directly from the website. Ask them to renew at the activation page.')
                                ->send();

                            return;
                        }

                        try {
                            $plan = Plan::findOrFail($data['plan_id']);
                            $service->activate($record, $plan, Filament::auth()->user());
                            Notification::make()->success()
                                ->title('Device activated')
                                ->body("Plan: {$plan->name} · {$plan->credit_cost} credits deducted")
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title('Activation failed')->body($e->getMessage())->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
        ];
    }
}
