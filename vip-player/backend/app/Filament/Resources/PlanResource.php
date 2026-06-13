<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Customer Plans';

    protected static ?string $navigationLabel = 'Plans';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Plan details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(100),
                    Forms\Components\Select::make('plan_type')
                        ->label('Plan type')
                        ->options([
                            'customer' => 'Customer (website activation page)',
                            'reseller' => 'Reseller (credit-based activation)',
                        ])
                        ->default('customer')
                        ->required()
                        ->live()
                        ->native(false),
                    Forms\Components\TextInput::make('duration_days')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->suffix('days')
                        ->visible(fn (Get $get) => ! $get('is_lifetime')),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_lifetime')
                        ->label('Lifetime plan')
                        ->inline(false)
                        ->visible(fn (Get $get) => $get('plan_type') === 'customer')
                        ->live(),
                    Forms\Components\Toggle::make('is_trial')
                        ->inline(false)
                        ->visible(fn (Get $get) => $get('plan_type') === 'reseller'),
                    Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                ]),
            Forms\Components\Section::make('Pricing')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('price_usd')
                        ->label('Price (USD)')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0)
                        ->step(0.01)
                        ->visible(fn (Get $get) => $get('plan_type') === 'customer')
                        ->helperText('Shown on the website activation page.'),
                    Forms\Components\TextInput::make('credit_cost')
                        ->label('Credit cost')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->suffix('credits')
                        ->visible(fn (Get $get) => $get('plan_type') === 'reseller')
                        ->helperText('Credits deducted when a reseller activates a device.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('plan_type')
                    ->badge()
                    ->color(fn (string $state) => $state === 'customer' ? 'info' : 'warning')
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state, Plan $record) => $record->is_lifetime ? 'Lifetime' : "{$state} days")
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_usd')
                    ->label('Price')
                    ->money('usd')
                    ->placeholder('—')
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('credit_cost')
                    ->label('Credits')
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state == 0 ? 'FREE' : "{$state} credits"),
                Tables\Columns\IconColumn::make('is_lifetime')->boolean()->label('Lifetime'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan_type')
                    ->label('Type')
                    ->options([
                        'customer' => 'Customer plans',
                        'reseller' => 'Reseller plans',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
