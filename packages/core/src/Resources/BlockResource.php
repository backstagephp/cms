<?php

namespace Backstage\Resources;

use Backstage\Facades\Backstage;
use Backstage\Fields\Filament\RelationManagers\FieldsRelationManager;
use Backstage\Models\Block;
use Backstage\Resources\BlockResource\Pages\CreateBlock;
use Backstage\Resources\BlockResource\Pages\EditBlock;
use Backstage\Resources\BlockResource\Pages\ListBlocks;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BlockResource extends Resource
{
    protected static ?string $model = Block::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    public static ?string $recordTitleAttribute = 'name';

    protected static ?string $tenantOwnershipRelationshipName = 'sites';

    public static function getNavigationGroup(): ?string
    {
        return __('Structure');
    }

    public static function getModelLabel(): string
    {
        return __('Block');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Blocks');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'block';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Block')
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old, ?Block $record) {
                                        $currentSlug = $get('slug');

                                        if (! $record?->slug && (! $currentSlug || $currentSlug === Str::slug($old))) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                ToggleButtons::make('icon')
                                    ->columnSpanFull()
                                    ->default('circle-stack')
                                    ->options([
                                        'circle-stack' => '',
                                        'light-bulb' => '',
                                    ])
                                    ->icons([
                                        'circle-stack' => 'heroicon-o-circle-stack',
                                        'light-bulb' => 'heroicon-o-light-bulb',
                                    ])
                                    ->inline()
                                    ->grouped()
                                    ->required(),
                                TextInput::make('slug')
                                    ->columnSpanFull()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),
                        Tab::make('Component')
                            ->schema([
                                Select::make('component')
                                    ->columnSpanFull()
                                    ->options(Backstage::getComponentOptions()),
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
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            $records->each(fn (Block $record) => $record->sites()->detach());
                        }),
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
            'index' => ListBlocks::route('/'),
            'create' => CreateBlock::route('/create'),
            'edit' => EditBlock::route('/{record}/edit'),
        ];
    }
}
