<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppSettingResource\Pages;
use App\Models\AppSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Support & Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'App Setting';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('key')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->disabledOn('edit'),
                    Forms\Components\TextInput::make('group')->default('general'),
                    Forms\Components\TextInput::make('label')->columnSpanFull(),
                    Forms\Components\Textarea::make('value')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->searchable()->fontFamily('mono')->weight('bold'),
                Tables\Columns\TextColumn::make('label')->limit(50),
                Tables\Columns\TextColumn::make('value')->limit(50)->placeholder('—'),
                Tables\Columns\TextColumn::make('group')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options(fn () => AppSetting::query()->distinct()->pluck('group', 'group')->all()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('group');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppSettings::route('/'),
            'create' => Pages\CreateAppSetting::route('/create'),
            'edit' => Pages\EditAppSetting::route('/{record}/edit'),
        ];
    }
}
