<?php

namespace App\Filament\Resources\DeviceResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name'),
                Tables\Columns\TextColumn::make('reseller.name')->placeholder('Admin'),
                Tables\Columns\TextColumn::make('starts_at')->dateTime(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime(),
                Tables\Columns\TextColumn::make('credits_spent'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
