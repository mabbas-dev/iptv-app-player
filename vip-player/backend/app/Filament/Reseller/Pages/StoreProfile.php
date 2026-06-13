<?php

namespace App\Filament\Reseller\Pages;

use App\Models\Reseller;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class StoreProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'My Store';

    protected static ?string $title = 'Store Profile';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.reseller.pages.store-profile';

    public ?array $data = [];

    public function mount(): void
    {
        /** @var Reseller $reseller */
        $reseller = Filament::auth()->user();

        $this->form->fill([
            'store_name' => $reseller->store_name,
            'store_description' => $reseller->store_description,
            'store_image' => $reseller->store_image ? [$reseller->store_image] : [],
            'store_url' => $reseller->store_url,
            'store_whatsapp' => $reseller->store_whatsapp,
            'store_email' => $reseller->store_email ?? $reseller->email,
            'show_in_directory' => $reseller->show_in_directory,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Public Store')
                ->description('Your store appears in the Official Resellers section on our website when enabled.')
                ->schema([
                    Toggle::make('show_in_directory')
                        ->label('Show my store on the official resellers page')
                        ->helperText('Requires a store name and description.'),
                    TextInput::make('store_name')
                        ->label('Store name')
                        ->required()
                        ->maxLength(120),
                    Textarea::make('store_description')
                        ->label('Store description')
                        ->rows(4)
                        ->maxLength(2000)
                        ->helperText('Tell customers what you offer — activation, support, plans, etc.'),
                    FileUpload::make('store_image')
                        ->label('Store logo / image')
                        ->image()
                        ->directory('reseller-stores')
                        ->disk('public')
                        ->maxSize(2048)
                        ->imageEditor(),
                ]),
            Section::make('Contact & Links')
                ->columns(2)
                ->schema([
                    TextInput::make('store_url')->label('Website URL')->url()->maxLength(255),
                    TextInput::make('store_email')->label('Contact email')->email(),
                    TextInput::make('store_whatsapp')->label('WhatsApp')->tel(),
                ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        /** @var Reseller $reseller */
        $reseller = Filament::auth()->user();
        $data = $this->form->getState();

        $image = is_array($data['store_image'] ?? null)
            ? ($data['store_image'][0] ?? null)
            : ($data['store_image'] ?? null);

        $storeName = trim($data['store_name'] ?? '');
        $slug = $storeName ? Str::slug($storeName) : null;

        if ($slug) {
            $base = $slug;
            $i = 1;
            while (
                Reseller::where('store_slug', $slug)
                    ->where('id', '!=', $reseller->id)
                    ->exists()
            ) {
                $slug = $base.'-'.$i;
                $i++;
            }
        }

        $reseller->update([
            'store_name' => $storeName ?: null,
            'store_slug' => $slug,
            'store_description' => $data['store_description'] ?? null,
            'store_image' => $image,
            'store_url' => $data['store_url'] ?? null,
            'store_whatsapp' => $data['store_whatsapp'] ?? null,
            'store_email' => $data['store_email'] ?? null,
            'show_in_directory' => (bool) ($data['show_in_directory'] ?? false) && filled($storeName),
        ]);

        Notification::make()->success()->title('Store profile saved')->send();
    }
}
