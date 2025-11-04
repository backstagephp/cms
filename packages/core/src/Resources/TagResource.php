<?php

namespace Backstage\Resources;

use Backstage\Models\Tag;
use Backstage\Resources\TagResource\Pages\CreateTag;
use Backstage\Resources\TagResource\Pages\EditTag;
use Backstage\Resources\TagResource\Pages\ListTags;
use Backstage\View\Components\Filament\Badge;
use Backstage\View\Components\Filament\BadgeableColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    public static ?string $recordTitleAttribute = 'name';

    protected static ?string $tenantOwnershipRelationshipName = 'sites';

    public static function getModelLabel(): string
    {
        return __('Tag');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tags');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeableColumn::make('name')
                    ->formatStateUsing(fn () => '')
                    ->searchable()
                    ->sortable()
                    ->separator('')
                    ->suffixBadges([
                        Badge::make('type')
                            ->label(fn (Tag $record) => '#' . $record->name)
                            ->color('gray'),
                    ])
                    ->url(fn (Tag $record) => route('filament.backstage.resources.content.index', [
                        'tenant' => Filament::getTenant(),
                        'filters[tags][values]' => [$record->getKey()],
                    ])),
                TextColumn::make('content_count')
                    ->label('Times used')
                    ->counts('content')
                    ->url(fn (Tag $record) => route('filament.backstage.resources.content.index', [
                        'tenant' => Filament::getTenant(),
                        'filters[tags][values]' => [$record->getKey()],
                    ])),
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
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
