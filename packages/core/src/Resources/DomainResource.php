<?php

namespace Backstage\Resources;

use Backstage\Models\Domain;
use Backstage\Resources\DomainResource\Pages\CreateDomain;
use Backstage\Resources\DomainResource\Pages\EditDomain;
use Backstage\Resources\DomainResource\Pages\ListDomains;
use Backstage\Resources\DomainResource\RelationManagers\LanguagesRelationManager;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';

    public static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationParentItem = 'Sites';

    public static function getNavigationGroup(): ?string
    {
        return __('Manage');
    }

    public static function getModelLabel(): string
    {
        return __('Domain');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Domains');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Domain')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Domain name')
                                    ->columnSpanFull()
                                    ->afterStateUpdated(fn (string $state): string => preg_replace('/^(http)(s)?:\/\//i', '', $state))
                                    ->placeholder('example.com')
                                    ->default(parse_url(config('app.url', ''))['host'] ?? '')
                                    ->required(),
                                Select::make('environment')
                                    ->label('Environment')
                                    ->columnSpanFull()
                                    ->default(config('app.env', 'local'))
                                    ->options([
                                        'local' => __('Local'),
                                        'production' => __('Production'),
                                        'staging' => __('Staging'),
                                    ])
                                    ->required(),
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
                TextColumn::make('environment')
                    ->badge()
                    ->color(fn (Domain $domain) => match ($domain->environment) {
                        'production' => 'success',
                        'staging' => 'warning',
                        'local' => 'red',
                    })
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
            LanguagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDomains::route('/'),
            'create' => CreateDomain::route('/create'),
            'edit' => EditDomain::route('/{record}/edit'),
        ];
    }
}
