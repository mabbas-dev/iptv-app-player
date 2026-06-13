<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditTransactionResource\Pages;
use App\Models\CreditTransaction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CreditTransactionResource extends Resource
{
    protected static ?string $model = CreditTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Resellers & Credits';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime('M j, Y H:i')->sortable()->label('Date'),
                Tables\Columns\TextColumn::make('reseller.name')->searchable()->label('Reseller'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        CreditTransaction::TYPE_CREDIT, CreditTransaction::TYPE_PURCHASE, CreditTransaction::TYPE_REFUND => 'success',
                        CreditTransaction::TYPE_DEBIT => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->weight('bold')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => ($state >= 0 ? '+' : '') . $state),
                Tables\Columns\TextColumn::make('balance_after')->label('Balance after'),
                Tables\Columns\TextColumn::make('description')->limit(50)->placeholder('—'),
                Tables\Columns\TextColumn::make('admin.name')->label('By admin')->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(array_combine(CreditTransaction::TYPES, array_map('ucfirst', CreditTransaction::TYPES))),
                Tables\Filters\SelectFilter::make('reseller_id')->relationship('reseller', 'name')->label('Reseller'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditTransactions::route('/'),
        ];
    }
}
