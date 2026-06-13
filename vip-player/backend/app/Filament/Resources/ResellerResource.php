<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResellerResource\Pages;
use App\Models\CreditTransaction;
use App\Models\Reseller;
use App\Services\CreditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class ResellerResource extends Resource
{
    protected static ?string $model = Reseller::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Resellers & Credits';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Reseller Account')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('phone')->tel()->maxLength(30),
                    Forms\Components\TextInput::make('company_name')->maxLength(255),
                    Forms\Components\Select::make('status')
                        ->options([
                            Reseller::STATUS_PENDING => 'Pending',
                            Reseller::STATUS_ACTIVE => 'Active',
                            Reseller::STATUS_SUSPENDED => 'Suspended',
                        ])
                        ->default(Reseller::STATUS_ACTIVE)
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->helperText('Leave empty to keep the current password.'),
                ]),
            Forms\Components\Section::make('Public Store')
                ->description('Shown on the official resellers page when enabled.')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('show_in_directory')->label('Show in directory'),
                    Forms\Components\TextInput::make('store_name')->maxLength(120),
                    Forms\Components\TextInput::make('store_slug')->maxLength(120)->unique(ignoreRecord: true),
                    Forms\Components\FileUpload::make('store_image')->image()->directory('reseller-stores')->disk('public'),
                    Forms\Components\Textarea::make('store_description')->rows(3)->columnSpanFull(),
                    Forms\Components\TextInput::make('store_url')->url(),
                    Forms\Components\TextInput::make('store_email')->email(),
                    Forms\Components\TextInput::make('store_whatsapp')->tel(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('email')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('company_name')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Reseller::STATUS_ACTIVE => 'success',
                        Reseller::STATUS_PENDING => 'warning',
                        Reseller::STATUS_SUSPENDED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('wallet.balance')
                    ->label('Credits')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('devices_count')
                    ->counts('devices')
                    ->label('Devices'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M j, Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Reseller::STATUS_PENDING => 'Pending',
                        Reseller::STATUS_ACTIVE => 'Active',
                        Reseller::STATUS_SUSPENDED => 'Suspended',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('add_credits')
                    ->label('Add Credits')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()->required()->minValue(1)->maxValue(100000)
                            ->suffix('credits'),
                        Forms\Components\TextInput::make('description')
                            ->placeholder('e.g. Manual top-up by admin'),
                    ])
                    ->action(function (Reseller $record, array $data, CreditService $service) {
                        $service->credit(
                            $record,
                            (int) $data['amount'],
                            CreditTransaction::TYPE_CREDIT,
                            $data['description'] ?: 'Manual credit by admin',
                            auth()->id(),
                        );
                        Notification::make()->success()
                            ->title("Added {$data['amount']} credits to {$record->name}")
                            ->send();
                    }),
                Tables\Actions\Action::make('deduct_credits')
                    ->label('Deduct')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()->required()->minValue(1)
                            ->suffix('credits'),
                        Forms\Components\TextInput::make('description')
                            ->placeholder('e.g. Adjustment'),
                    ])
                    ->action(function (Reseller $record, array $data, CreditService $service) {
                        try {
                            $service->debit(
                                $record,
                                (int) $data['amount'],
                                CreditTransaction::TYPE_ADJUSTMENT,
                                $data['description'] ?: 'Manual deduction by admin',
                                adminId: auth()->id(),
                            );
                            Notification::make()->success()->title('Credits deducted')->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title('Failed')->body($e->getMessage())->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
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
            ResellerResource\RelationManagers\CreditTransactionsRelationManager::class,
            ResellerResource\RelationManagers\DevicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResellers::route('/'),
            'create' => Pages\CreateReseller::route('/create'),
            'edit' => Pages\EditReseller::route('/{record}/edit'),
        ];
    }
}
