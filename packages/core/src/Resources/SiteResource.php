<?php

namespace Backstage\Resources;

use Backstage\Models\Site;
use Backstage\Resources\SiteResource\Pages\CreateSite;
use Backstage\Resources\SiteResource\Pages\EditSite;
use Backstage\Resources\SiteResource\Pages\ListSites;
use DateTimeZone;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-window';

    public static ?string $recordTitleAttribute = 'name';

    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): ?string
    {
        return __('Manage');
    }

    public static function getModelLabel(): string
    {
        return __('Site');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Sites');
    }

    public static function form(Schema $schema, bool $fullWidth = false): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Site')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    TextInput::make('name')
                                        ->label('Name')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->helperText('The name of the site, used internally in the back-end only.')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, ?string $state) {
                                            $set('slug', Str::slug($state));
                                            $set('name_plural', Str::plural($state));
                                            $set('title', Str::title($state));
                                        })
                                        ->required(),
                                    TextInput::make('slug')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->helperText('The slug for this site, used in code.')
                                        ->required(),
                                    TextInput::make('title')
                                        ->label('Title')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->helperText('The title of the site, used in the front-end for SEO.'),
                                    TextInput::make('title_separator')
                                        ->label('Title separator')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->default('|')
                                        ->hint('E.g. "Page Title | Name of site"')
                                        ->helperText('Symbol between page title and site name.'),
                                    Select::make('theme')
                                        ->label('Theme')
                                        ->default('system')
                                        ->options([
                                            'system' => 'System',
                                            'light' => 'Light',
                                            'dark' => 'Dark',
                                        ])
                                        ->nullable()
                                        ->columnSpanFull()
                                        ->helperText('Select default theme.'),
                                    Toggle::make('default')
                                        ->label('Use this site as default.')
                                        ->default(Site::default()?->ulid === null)
                                        ->columnSpanFull(),
                                    Toggle::make('auth')
                                        ->label('Protect site behind authentication.')
                                        ->columnSpanFull(),
                                    Toggle::make('two_factor_required')
                                        ->label('Require 2FA for authentication.')
                                        ->columnSpanFull()
                                        ->helperText('When enabled, users must set up and use two-factor authentication to access this site.'),
                                ]),
                            ]),
                        Tab::make('Branding')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    ColorPicker::make('primary_color')
                                        ->label('Primary color')
                                        ->columnSpanFull()
                                        ->helperText('Pick a custom color for the site.')
                                        ->nullable(),
                                    FileUpload::make('logo')
                                        ->label('Logo')
                                        ->columnSpanFull()
                                        ->helperText('Upload a logo for the site.'),
                                ]),
                            ]),
                        Tab::make('Path')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    TextInput::make('path')
                                        ->label('Path')
                                        ->prefix('/')
                                        ->columnSpanFull()
                                        ->helperText('Set starting path for the site, e.g. "/department"'),
                                    Toggle::make('trailing_slash')
                                        ->label('Always generate and redirect URLs using a trailing slash.')
                                        ->columnSpanFull(),
                                ]),
                            ]),
                        Tab::make('Locale')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    Select::make('timezone')
                                        ->label('Timezone')
                                        ->columnSpanFull()
                                        ->searchable()
                                        ->preload()
                                        ->helperText('Default timezone used for displaying date and time.')
                                        ->options(
                                            collect([
                                                'Africa' => DateTimeZone::AFRICA,
                                                'America' => DateTimeZone::AMERICA,
                                                'Asia' => DateTimeZone::ASIA,
                                                'Europe' => DateTimeZone::EUROPE,
                                                'Oceania' => DateTimeZone::AUSTRALIA,
                                                'UTC' => DateTimeZone::UTC,
                                            ])->map(function ($code) {
                                                return collect(DateTimeZone::listIdentifiers($code))->mapWithKeys(fn ($code) => [$code => $code]);
                                            })
                                        )
                                        ->default(config('app.timezone'))
                                        ->required(),
                                ]),
                            ]),
                        Tab::make('Email')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    TextInput::make('email_from_name')
                                        ->label('Email From Name')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->helperText('Default name to use in email.')
                                        ->required(false),
                                    TextInput::make('email_from_domain')
                                        ->label('Email From Domain')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->helperText('Default domain to use for sending email.')
                                        ->required(false),
                                ]),
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
                IconColumn::make('default')
                    ->label('Default')
                    ->width(1)
                    ->boolean(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSites::route('/'),
            'create' => CreateSite::route('/create'),
            'edit' => EditSite::route('/{record}/edit'),
        ];
    }
}
