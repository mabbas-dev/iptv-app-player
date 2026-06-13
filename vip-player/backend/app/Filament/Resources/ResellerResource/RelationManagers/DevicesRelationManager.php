<?php

namespace App\Filament\Resources\ResellerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DevicesRelationManager extends RelationManager
{
    protected static string $relationship = 'devices';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device_code')->fontFamily('mono'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('subscription_ends_at')->dateTime()->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
