<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Devices & Subscriptions';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device.device_code')
                    ->label('Device')
                    ->fontFamily('mono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan.name')->label('Plan'),
                Tables\Columns\TextColumn::make('reseller.name')->placeholder('Admin')->label('Activated by'),
                Tables\Columns\TextColumn::make('starts_at')->dateTime('M j, Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime('M j, Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('credits_spent')->badge()->color('warning'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Subscription::STATUS_ACTIVE => 'success',
                        Subscription::STATUS_EXPIRED => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Subscription::STATUS_ACTIVE => 'Active',
                        Subscription::STATUS_EXPIRED => 'Expired',
                        Subscription::STATUS_CANCELLED => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('plan_id')->relationship('plan', 'name')->label('Plan'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
        ];
    }
}
