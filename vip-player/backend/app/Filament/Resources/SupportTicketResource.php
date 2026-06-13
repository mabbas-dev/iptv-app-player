<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';

    protected static ?string $navigationGroup = 'Support & Settings';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $open = static::getModel()::where('status', SupportTicket::STATUS_OPEN)->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Ticket')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('subject')->required()->columnSpanFull(),
                    Forms\Components\Textarea::make('message')->rows(5)->required()->columnSpanFull(),
                    Forms\Components\TextInput::make('name'),
                    Forms\Components\TextInput::make('email')->email(),
                    Forms\Components\Select::make('device_id')
                        ->relationship('device', 'device_code')
                        ->searchable()
                        ->nullable(),
                    Forms\Components\Select::make('status')
                        ->options([
                            SupportTicket::STATUS_OPEN => 'Open',
                            SupportTicket::STATUS_IN_PROGRESS => 'In Progress',
                            SupportTicket::STATUS_CLOSED => 'Closed',
                        ])
                        ->required()
                        ->native(false),
                ]),
            Forms\Components\Section::make('Admin Reply')
                ->schema([
                    Forms\Components\Textarea::make('admin_reply')
                        ->rows(4)
                        ->helperText('This reply is shown to the user in the app support screen.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')->searchable()->weight('bold')->limit(40),
                Tables\Columns\TextColumn::make('device.device_code')->fontFamily('mono')->placeholder('—')->label('Device'),
                Tables\Columns\TextColumn::make('email')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        SupportTicket::STATUS_OPEN => 'danger',
                        SupportTicket::STATUS_IN_PROGRESS => 'warning',
                        SupportTicket::STATUS_CLOSED => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        SupportTicket::STATUS_OPEN => 'Open',
                        SupportTicket::STATUS_IN_PROGRESS => 'In Progress',
                        SupportTicket::STATUS_CLOSED => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('close')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SupportTicket $record) => $record->status !== SupportTicket::STATUS_CLOSED)
                    ->action(fn (SupportTicket $record) => $record->update(['status' => SupportTicket::STATUS_CLOSED])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'create' => Pages\CreateSupportTicket::route('/create'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
