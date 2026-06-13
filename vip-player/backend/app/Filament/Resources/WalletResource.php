<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Models\Wallet;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationGroup = 'Resellers & Credits';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reseller.name')
                    ->label('Reseller')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('reseller.email')->label('Email'),
                Tables\Columns\TextColumn::make('balance')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->suffix(' credits')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Last activity')->since(),
            ])
            ->defaultSort('balance', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWallets::route('/'),
        ];
    }
}
