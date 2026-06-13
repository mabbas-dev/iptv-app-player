<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaylistResource\Pages;
use App\Models\Playlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlaylistResource extends Resource
{
    protected static ?string $model = Playlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Devices & Subscriptions';

    protected static ?string $navigationLabel = 'User Uploads';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Playlist')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->options([
                            Playlist::TYPE_XTREAM => 'Xtream Codes',
                            Playlist::TYPE_M3U => 'M3U URL',
                            Playlist::TYPE_M3U8 => 'M3U8 URL',
                            Playlist::TYPE_DIRECT => 'Direct URL',
                        ])
                        ->required()
                        ->live()
                        ->native(false),
                    Forms\Components\Select::make('reseller_id')
                        ->relationship('reseller', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->helperText('Optional: owner reseller.'),
                    Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                ]),
            Forms\Components\Section::make('Xtream Login')
                ->columns(3)
                ->visible(fn (Get $get) => $get('type') === Playlist::TYPE_XTREAM)
                ->schema([
                    Forms\Components\TextInput::make('server_url')
                        ->label('Server URL')
                        ->url()
                        ->placeholder('http://example.com:8080')
                        ->required(fn (Get $get) => $get('type') === Playlist::TYPE_XTREAM),
                    Forms\Components\TextInput::make('username'),
                    Forms\Components\TextInput::make('password'),
                ]),
            Forms\Components\Section::make('URL / File')
                ->columns(1)
                ->visible(fn (Get $get) => $get('type') !== Playlist::TYPE_XTREAM)
                ->schema([
                    Forms\Components\TextInput::make('url')
                        ->label('Playlist URL')
                        ->url()
                        ->placeholder('https://example.com/playlist.m3u'),
                    Forms\Components\FileUpload::make('file_path')
                        ->label('Or upload playlist file')
                        ->directory('playlists')
                        ->disk('public')
                        ->acceptedFileTypes(['audio/x-mpegurl', 'application/x-mpegurl', 'application/vnd.apple.mpegurl', 'text/plain', 'application/octet-stream'])
                        ->maxSize(10240)
                        ->helperText('Upload an .m3u / .m3u8 file instead of a URL.'),
                ]),
            Forms\Components\Section::make('EPG')
                ->schema([
                    Forms\Components\TextInput::make('epg_url')
                        ->label('EPG URL (XMLTV)')
                        ->url()
                        ->placeholder('https://example.com/epg.xml'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Playlist::TYPE_XTREAM => 'success',
                        Playlist::TYPE_M3U => 'info',
                        Playlist::TYPE_M3U8 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reseller.name')->placeholder('Admin')->label('Owner'),
                Tables\Columns\TextColumn::make('devices_count')->counts('devices')->label('Devices'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M j, Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(array_combine(Playlist::TYPES, Playlist::TYPES)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaylists::route('/'),
            'create' => Pages\CreatePlaylist::route('/create'),
            'edit' => Pages\EditPlaylist::route('/{record}/edit'),
        ];
    }
}
