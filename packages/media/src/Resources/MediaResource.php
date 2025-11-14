<?php

namespace Backstage\Media\Resources;

use Backstage\Media\Components\Media;
use Backstage\Media\MediaPlugin;
use Backstage\Media\Resources\MediaResource\CreateMedia;
use Backstage\Media\Resources\MediaResource\EditMedia;
use Backstage\Media\Resources\MediaResource\ListMedia;
use Backstage\Translations\Laravel\Facades\Translator;
use Backstage\Translations\Laravel\Models\Language;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Facades\Filament;
use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Phiki\Grammar\Grammar;

class MediaResource extends Resource
{
    public static function getModel(): string
    {
        return config('backstage.media.model');
    }

    public static function isScopedToTenant(): bool
    {
        return config('backstage.media.is_tenant_aware') ?? static::$isScopedToTenant;
    }

    public static function getTenantOwnershipRelationshipName(): string
    {
        return config('backstage.media.tenant_ownership_relationship_name') ?? Filament::getTenantOwnershipRelationshipName();
    }

    public static function getModelLabel(): string
    {
        return MediaPlugin::get()->getLabel();
    }

    public static function getPluralModelLabel(): string
    {
        return MediaPlugin::get()->getPluralLabel();
    }

    public static function getNavigationLabel(): string
    {
        return MediaPlugin::get()->getNavigationLabel() ?: (Str::title(static::getPluralModelLabel()) ?: Str::title(static::getModelLabel()));
    }

    public static function getNavigationIcon(): string
    {
        return MediaPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return MediaPlugin::get()->getNavigationSort();
    }

    public static function getNavigationGroup(): ?string
    {
        return MediaPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationBadge(): ?string
    {
        if (! MediaPlugin::get()->getNavigationCountBadge()) {
            return null;
        }

        if (Filament::hasTenancy() && config('backstage.media.is_tenant_aware')) {
            $tenant = Filament::getTenant();
            $tenantId = $tenant && property_exists($tenant, 'id') ? $tenant->id : null;
            $count = static::getEloquentQuery()
                ->where(config('backstage.media.tenant_relationship') . '_ulid', $tenantId)
                ->count();

            return (string) $count;
        }

        return (string) static::getModel()::count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return MediaPlugin::get()->shouldRegisterNavigation();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Media::make()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_filename')
                    ->label(__('Filename'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('extension')
                    ->label(__('Extension'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('public')
                    ->boolean()
                    ->label(__('Public'))
                    ->sortable(),

            ])
            ->recordActions([
                ViewAction::make()
                    ->hiddenLabel()
                    ->tooltip(__('View'))
                    ->slideOver()
                    ->modalHeading(fn ($record) => $record->original_filename)
                    ->schema([
                        ...self::getFormSchema(),
                    ]),
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(__('Edit'))
                    ->slideOver()
                    ->modalHeading(fn ($record) => $record->original_filename)
                    ->url(false)
                    ->fillForm(fn ($record) => self::getEditFormData($record))
                    ->action(fn (array $data, $record) => self::saveEditForm($data, $record))
                    ->form(fn () => self::getEditFormSchema()),
                DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip(__('Delete')),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([6, 12, 24, 48, 'all'])
            ->recordUrl(false);
    }

    public static function getFormSchema(): array
    {
        $schema = [
            Section::make(__('File Information'))
                ->schema([
                    TextEntry::make('original_filename')
                        ->label(__('Original Filename'))
                        ->copyable(),
                    TextEntry::make('filename')
                        ->label(__('Filename'))
                        ->copyable(),
                    TextEntry::make('extension')
                        ->label(__('Extension'))
                        ->badge(),
                    TextEntry::make('mime_type')
                        ->label(__('MIME Type'))
                        ->badge(),
                    TextEntry::make('size')
                        ->label(__('File Size'))
                        ->formatStateUsing(function ($state) {
                            if (! $state) {
                                return null;
                            }

                            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                            $bytes = (int) $state;

                            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                                $bytes /= 1024;
                            }

                            return round($bytes, 2) . ' ' . $units[$i];
                        }),
                    IconEntry::make('public')
                        ->label(__('Public'))
                        ->boolean(),
                ])
                ->columns(2),

            Section::make(__('File Preview'))
                ->schema([
                    ImageEntry::make('url')
                        ->label(__('Preview'))
                        ->imageHeight(200)
                        ->visible(fn ($record) => $record && $record->mime_type && str_starts_with($record->mime_type, 'image/')),
                    TextEntry::make('url')
                        ->label(__('File URL'))
                        ->copyable()
                        ->url(fn ($state) => $state)
                        ->openUrlInNewTab(),
                ]),

            Section::make(__('Alt Text'))
                ->schema(function () {
                    try {
                        $languages = Language::all();
                        if ($languages->isEmpty()) {
                            return [
                                TextEntry::make('alt')
                                    ->label(__('Alt Text'))
                                    ->placeholder(__('No alt text set'))
                                    ->columnSpanFull(),
                            ];
                        }

                        $defaultLanguage = $languages->firstWhere('default', true);
                        $otherLanguages = $languages->where('default', false);
                        $entries = [];

                        // Add default language
                        if ($defaultLanguage) {
                            $code = $defaultLanguage->code;
                            $entries[] = TextEntry::make("alt_default_{$code}")
                                ->label(__('Alt Text') . ' (' . strtoupper($code) . ')')
                                ->state(function ($record) use ($code) {
                                    if (method_exists($record, 'getTranslatedAttribute')) {
                                        return $record->getTranslatedAttribute('alt', $code) ?? '';
                                    }
                                    return $record->alt ?? '';
                                })
                                ->icon(country_flag($code))
                                ->placeholder(__('No alt text set'))
                                ->columnSpanFull();
                        }

                        // Add other languages
                        foreach ($otherLanguages as $language) {
                            $code = $language->code;
                            $entries[] = TextEntry::make("alt_lang_{$code}")
                                ->label(__('Alt Text') . ' (' . strtoupper($code) . ')')
                                ->state(function ($record) use ($code) {
                                    if (method_exists($record, 'getTranslatedAttribute')) {
                                        return $record->getTranslatedAttribute('alt', $code) ?? '';
                                    }
                                    return '';
                                })
                                ->icon(country_flag($code))
                                ->placeholder(__('No translation'))
                                ->columnSpanFull();
                        }

                        return $entries;
                    } catch (\Exception $e) {
                        return [
                            TextEntry::make('alt')
                                ->label(__('Alt Text'))
                                ->placeholder(__('No alt text set'))
                                ->columnSpanFull(),
                        ];
                    }
                })
                ->collapsible(),

            Section::make(__('Technical Details'))
                ->schema([
                    TextEntry::make('disk')
                        ->label(__('Storage Disk'))
                        ->badge(),
                    TextEntry::make('checksum')
                        ->label(__('Checksum'))
                        ->copyable()
                        ->visible(fn ($record) => $record && $record->checksum),
                    TextEntry::make('width')
                        ->label(__('Width'))
                        ->visible(fn ($record) => $record && $record->width)
                        ->suffix('px'),
                    TextEntry::make('height')
                        ->label(__('Height'))
                        ->visible(fn ($record) => $record && $record->height)
                        ->suffix('px'),
                    TextEntry::make('created_at')
                        ->label(__('Created At'))
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->label(__('Updated At'))
                        ->dateTime(),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make(__('Metadata'))
                ->schema([
                    CodeEntry::make('metadata')
                        ->label(__('Metadata'))
                        ->hiddenLabel()
                        ->formatStateUsing(function ($state) {
                            if (! $state) {
                                return null;
                            }

                            // If it's already a string, try to decode it
                            if (is_string($state)) {
                                $decoded = json_decode($state, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $state = $decoded;
                                } else {
                                    // If it's not valid JSON, return as-is
                                    return $state;
                                }
                            }

                            if (empty($state)) {
                                return null;
                            }

                            // Ensure proper JSON formatting with indentation
                            return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        })
                        ->grammar(Grammar::Json)
                        ->visible(fn ($record) => $record && $record->metadata)
                        ->columnSpanFull()
                        ->copyable(),
                ])
                ->collapsible(),
        ];

        return $schema;
    }

    private static function getEditFormSchema(): array
    {
        // Build alt text fields
        $altTextFields = [];

        try {
            $languages = Language::all();

            if (!$languages->isEmpty()) {
                $defaultLanguage = $languages->firstWhere('default', true);
                $otherLanguages = $languages->where('default', false);

                // Add default language alt text
                if ($defaultLanguage) {
                    $altTextFields[] = TextInput::make('alt')
                        ->label(__('Alt Text') . ' (' . strtoupper($defaultLanguage->code) . ')')
                        ->prefixIcon(country_flag($defaultLanguage->code), true)
                        ->helperText(__('The alt text for the media in the default language. We can automatically translate this to other languages using AI.'))
                        ->columnSpanFull();
                }

                // Add other languages
                foreach ($otherLanguages as $language) {
                    $altTextFields[] = TextInput::make('alt_text_' . $language->code)
                        ->label(__('Alt Text') . ' (' . strtoupper($language->code) . ')')
                        ->suffixActions([
                            Action::make('translate_from_default')
                                ->icon(Heroicon::OutlinedLanguage)
                                ->tooltip(__('Translate from default language'))
                                ->action(function (Get $get, Set $set) use ($language) {
                                    $defaultAlt = $get('alt');
                                    if ($defaultAlt) {
                                        $translator = Translator::translate($defaultAlt, $language->code);
                                        $set('alt_text_' . $language->code, $translator);
                                    }
                                }),
                        ], true)
                        ->prefixIcon(country_flag($language->code), true)
                        ->columnSpanFull();
                }
            } else {
                // No languages configured, just add simple alt field
                $altTextFields[] = TextInput::make('alt')
                    ->label(__('Alt Text'))
                    ->columnSpanFull();
            }
        } catch (\Exception $e) {
            // Fallback to simple alt field if languages can't be loaded
            $altTextFields[] = TextInput::make('alt')
                ->label(__('Alt Text'))
                ->columnSpanFull();
        }

        return [
            Tabs::make('Edit Media')
                ->tabs([
                    Tabs\Tab::make(__('File Info'))
                        ->icon('heroicon-o-document')
                        ->schema([
                            TextInput::make('original_filename')
                                ->label(__('Original Filename'))
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ]),

                    Tabs\Tab::make(__('Alt Text'))
                        ->icon('heroicon-o-language')
                        ->schema($altTextFields),

                    Tabs\Tab::make(__('Metadata'))
                        ->icon('heroicon-o-code-bracket')
                        ->schema([
                            KeyValue::make('metadata')
                                ->label(__('Metadata'))
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }

    private static function getEditFormData($record): array
    {
        $data = [
            'original_filename' => $record->original_filename,
            'alt' => $record->alt ?? '',
            'metadata' => $record->metadata ?? [],
        ];

        // Load translations if supported
        if (method_exists($record, 'getTranslatedAttribute')) {
            try {
                $languages = Language::all();
                $defaultLanguage = $languages->firstWhere('default', true);
                $otherLanguages = $languages->where('default', false);

                if ($defaultLanguage) {
                    $data['alt'] = $record->getTranslatedAttribute('alt', $defaultLanguage->code) ?? '';
                }

                foreach ($otherLanguages as $language) {
                    $data['alt_text_' . $language->code] = $record->getTranslatedAttribute('alt', $language->code) ?? '';
                }
            } catch (\Exception $e) {
                // Continue with simple alt text
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @param \Backstage\Media\Models\Media $record
     * @return void
     */
    private static function saveEditForm(array $data, $record): void
    {
        // Debug: Log what we're receiving
        \Log::info('SaveEditForm called', [
            'data' => $data,
            'record_class' => get_class($record),
            'has_method' => method_exists($record, 'pushTranslateAttribute'),
        ]);

        // Update basic fields
        $updateData = [
            'original_filename' => $data['original_filename'],
            'metadata' => $data['metadata'] ?? null,
        ];

        // Check if model supports translations
        if (method_exists($record, 'pushTranslateAttribute')) {
            // Model has translation support
            $record->updateQuietly($updateData);

            try {
                $languages = Language::all();
                $defaultLanguage = $languages->firstWhere('default', true);
                $otherLanguages = $languages->where('default', false);

                \Log::info('Languages loaded', [
                    'default' => $defaultLanguage->code ?? 'none',
                    'others' => $otherLanguages->pluck('code')->toArray(),
                ]);

                // Save default language translation
                if ($defaultLanguage && isset($data['alt'])) {
                    // First update the base alt column
                    $record->updateQuietly(['alt' => $data['alt']]);
                    \Log::info('Pushing default translation', [
                        'code' => $defaultLanguage->code,
                        'value' => $data['alt'],
                    ]);
                    // Then push translation
                        $record->pushTranslateAttribute('alt', $data['alt'], $defaultLanguage->code);
                }

                // Save other language translations
                foreach ($otherLanguages as $language) {
                    $key = 'alt_text_' . $language->code;
                    if (isset($data[$key])) {
                            $record->pushTranslateAttribute('alt', $data[$key], $language->code);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Translation save error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            // Model doesn't support translations - update alt directly
            $updateData['alt'] = $data['alt'] ?? '';
            $record->updateQuietly($updateData);
        }

        Notification::make()
            ->title(__('Media updated'))
            ->body(__('The media has been updated successfully.'))
            ->success()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'edit' => EditMedia::route('/{record}/edit'),
        ];
    }
}