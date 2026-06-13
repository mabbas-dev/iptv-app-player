<?php

namespace App\Filament\Reseller\Resources;

use App\Filament\Reseller\Resources\CreditTransactionResource\Pages;
use App\Models\CreditTransaction;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CreditTransactionResource extends Resource
{
    protected static ?string $model = CreditTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('reseller_id', Filament::auth()->id());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime('M j, Y H:i')->label('Date')->sortable(),
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
                Tables\Columns\TextColumn::make('description')->limit(60)->placeholder('—'),
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
