<?php

namespace App\Filament\Resources\ResellerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CreditTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'creditTransactions';

    protected static ?string $title = 'Credit History';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Date'),
                Tables\Columns\TextColumn::make('type')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'credit', 'purchase', 'refund' => 'success',
                        'debit' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('balance_after')->label('Balance'),
                Tables\Columns\TextColumn::make('description')->limit(60),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
