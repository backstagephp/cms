<?php

namespace Backstage\Resources;

use Backstage\Fields\Filament\RelationManagers\FieldsRelationManager;
use Backstage\Models\Language;
use Backstage\Models\Setting;
use Backstage\Models\Site;
use Backstage\Resources\SettingResource\Pages\CreateSetting;
use Backstage\Resources\SettingResource\Pages\EditSetting;
use Backstage\Resources\SettingResource\Pages\ListSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    public static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('Manage');
    }

    public static function getModelLabel(): string
    {
        return __('Setting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Settings');
    }

    public static function fields(): array
    {
        return [
            TextInput::make('name')
                ->label(__('Name'))
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    $set('slug', Str::slug($state));
                }),
            TextInput::make('slug')
                ->label(__('Slug'))
                ->required()
                ->unique(ignoreRecord: true),

            Select::make('site_ulid')
                ->label(__('Site'))
                ->columnSpanFull()
                ->placeholder(__('Select Site'))
                ->prefixIcon('heroicon-o-link')
                ->options(Site::orderBy('default', 'desc')->orderBy('name', 'asc')->pluck('name', 'ulid'))
                ->default(Site::where('default', true)->first()?->ulid),

            Select::make('language_code')
                ->label(__('Language'))
                ->columnSpanFull()
                ->placeholder(__('Select Language'))
                ->prefixIcon('heroicon-o-language')
                ->options(
                    Language::active()
                        ->get()
                        ->sort()
                        ->groupBy(function ($language) {
                            return Str::contains($language->code, '-') ? localized_country_name($language->code) : __('Worldwide');
                        })
                        ->mapWithKeys(fn ($languages, $countryName) => [
                            $countryName => $languages->mapWithKeys(fn ($language) => [
                                $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(flag_path(explode('-', $language->code)[0]))) . '" class="inline-block relative w-5" style="top: -1px; margin-right: 3px;"> ' . localized_language_name($language->code) . ' (' . $countryName . ')',
                            ])->toArray(),
                        ])
                )
                ->allowHtml()
                ->visible(fn () => Language::active()->count() > 1),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Setting')
                            ->label(__('Setting'))
                            ->schema([
                                Grid::make()
                                    ->columns(2)
                                    ->schema(static::fields()),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('site.name')
                    ->label(__('Site'))
                    ->default('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('language_code')
                    ->label(__('Language'))
                    ->default('-')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            FieldsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSettings::route('/'),
            'create' => CreateSetting::route('/create'),
            'edit' => EditSetting::route('/{record}/edit'),
        ];
    }
}
