<?php

namespace App\Filament\Reseller\Resources;

use App\Filament\Reseller\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'API Keys & Docs';

    protected static ?string $navigationGroup = 'Integration';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('reseller_id', Filament::auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->placeholder('e.g. My website integration'),
            Forms\Components\TextInput::make('key')
                ->default(fn () => ApiKey::generateKey())
                ->readOnly()
                ->required()
                ->helperText('Copy this key now and keep it secret. See API documentation on the keys list page.'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold'),
                Tables\Columns\TextColumn::make('key')
                    ->fontFamily('mono')
                    ->limit(24)
                    ->copyable()
                    ->copyMessage('API key copied'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('last_used_at')->since()->placeholder('Never used'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
            'edit' => Pages\EditApiKey::route('/{record}/edit'),
        ];
    }
}
