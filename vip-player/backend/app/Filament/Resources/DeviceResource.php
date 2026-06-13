<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Models\Device;
use App\Models\Plan;
use App\Models\Playlist;
use App\Services\DeviceActivationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-tablet';

    protected static ?string $navigationGroup = 'Devices & Subscriptions';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Device')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('device_code')
                        ->label('Device ID (MAC-style)')
                        ->default(fn () => Device::generateDeviceCode())
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(17),
                    Forms\Components\TextInput::make('device_uuid')
                        ->label('Device UUID')
                        ->default(fn () => (string) Str::uuid())
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\Select::make('platform')
                        ->options([
                            'android' => 'Android Mobile',
                            'android_tv' => 'Android TV',
                        ])
                        ->default('android')
                        ->required(),
                    Forms\Components\TextInput::make('app_version')
                        ->maxLength(20),
                    Forms\Components\Select::make('status')
                        ->options(array_combine(Device::STATUSES, array_map('ucfirst', Device::STATUSES)))
                        ->default(Device::STATUS_NEW)
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('reseller_id')
                        ->relationship('reseller', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                ]),
            Forms\Components\Section::make('Trial & Subscription')
                ->columns(3)
                ->schema([
                    Forms\Components\DateTimePicker::make('trial_started_at'),
                    Forms\Components\DateTimePicker::make('trial_ends_at'),
                    Forms\Components\DateTimePicker::make('subscription_ends_at'),
                ]),
            Forms\Components\Section::make('Parental Lock')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('parental_lock_enabled')
                        ->label('Parental lock enabled'),
                    Forms\Components\TextInput::make('new_parental_pin')
                        ->label('Set new parental PIN')
                        ->password()
                        ->numeric()
                        ->minLength(4)
                        ->maxLength(8)
                        ->dehydrated(false)
                        ->helperText('Leave empty to keep current PIN. Stored hashed, never plain.'),
                ]),
        ]);
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
                    ->formatStateUsing(fn (string $state) => $state === 'android_tv' ? 'Android TV' : 'Android')
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Device::STATUS_ACTIVE => 'success',
                        Device::STATUS_TRIAL => 'warning',
                        Device::STATUS_NEW => 'gray',
                        Device::STATUS_EXPIRED => 'danger',
                        Device::STATUS_BLOCKED => 'danger',
                        Device::STATUS_SUSPENDED => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reseller.name')
                    ->label('Reseller')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Trial ends')
                    ->dateTime('M j, Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscription_ends_at')
                    ->label('Expires')
                    ->dateTime('M j, Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Last seen')
                    ->since()
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(array_combine(Device::STATUSES, array_map('ucfirst', Device::STATUSES))),
                Tables\Filters\SelectFilter::make('reseller_id')
                    ->relationship('reseller', 'name')
                    ->label('Reseller'),
                Tables\Filters\SelectFilter::make('platform')
                    ->options(['android' => 'Android Mobile', 'android_tv' => 'Android TV']),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('activate')
                        ->label('Activate / Renew')
                        ->icon('heroicon-o-bolt')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('plan_id')
                                ->label('Plan')
                                ->options(Plan::where('is_active', true)->where('plan_type', 'reseller')->orderBy('sort_order')->pluck('name', 'id'))
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (Device $record, array $data, DeviceActivationService $service) {
                            try {
                                $plan = Plan::findOrFail($data['plan_id']);
                                $service->activate($record, $plan, reseller: null, adminId: auth()->id());
                                Notification::make()->success()->title('Device activated')->body("Plan: {$plan->name}")->send();
                            } catch (\Throwable $e) {
                                Notification::make()->danger()->title('Activation failed')->body($e->getMessage())->send();
                            }
                        }),
                    Tables\Actions\Action::make('extend')
                        ->label('Extend Expiry')
                        ->icon('heroicon-o-calendar-days')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('days')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(3650)
                                ->suffix('days'),
                        ])
                        ->action(function (Device $record, array $data) {
                            $days = (int) $data['days'];
                            if ($record->status === Device::STATUS_TRIAL) {
                                $base = $record->trial_ends_at?->isFuture() ? $record->trial_ends_at : now();
                                $record->update(['trial_ends_at' => $base->copy()->addDays($days)]);
                            } else {
                                $base = $record->subscription_ends_at?->isFuture() ? $record->subscription_ends_at : now();
                                $record->update([
                                    'subscription_ends_at' => $base->copy()->addDays($days),
                                    'status' => Device::STATUS_ACTIVE,
                                ]);
                            }
                            Notification::make()->success()->title("Expiry extended by {$days} days")->send();
                        }),
                    Tables\Actions\Action::make('block')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Device $record) => $record->status !== Device::STATUS_BLOCKED)
                        ->action(function (Device $record) {
                            $record->update(['status' => Device::STATUS_BLOCKED]);
                            Notification::make()->success()->title('Device blocked')->send();
                        }),
                    Tables\Actions\Action::make('suspend')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (Device $record) => ! in_array($record->status, [Device::STATUS_SUSPENDED, Device::STATUS_BLOCKED], true))
                        ->action(function (Device $record) {
                            $record->update(['status' => Device::STATUS_SUSPENDED]);
                            Notification::make()->success()->title('Device suspended')->send();
                        }),
                    Tables\Actions\Action::make('unblock')
                        ->label('Restore')
                        ->icon('heroicon-o-play-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Device $record) => in_array($record->status, [Device::STATUS_BLOCKED, Device::STATUS_SUSPENDED], true))
                        ->action(function (Device $record) {
                            $status = Device::STATUS_EXPIRED;
                            if ($record->subscription_ends_at?->isFuture()) {
                                $status = Device::STATUS_ACTIVE;
                            } elseif ($record->trial_ends_at?->isFuture()) {
                                $status = Device::STATUS_TRIAL;
                            }
                            $record->update(['status' => $status]);
                            Notification::make()->success()->title('Device restored')->send();
                        }),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            DeviceResource\RelationManagers\PlaylistsRelationManager::class,
            DeviceResource\RelationManagers\SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
