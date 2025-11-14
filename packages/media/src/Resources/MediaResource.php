<?php

namespace Backstage\Media\Resources;

use Backstage\Media\Components\Media;
use Backstage\Media\MediaPlugin;
use Backstage\Media\Resources\MediaResource\CreateMedia;
use Backstage\Media\Resources\MediaResource\EditMedia;
use Backstage\Media\Resources\MediaResource\ListMedia;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
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
                    ->label(__('Original Filename'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('filename')
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
                    ->schema([
                        ...self::getFormSchema(),
                    ]),
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

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'edit' => EditMedia::route('/{record}/edit'),
        ];
    }
}