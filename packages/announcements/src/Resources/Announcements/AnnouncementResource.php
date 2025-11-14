<?php

namespace Backstage\Announcements\Resources\Announcements;

use BackedEnum;
use Backstage\Announcements\Models\Announcement;
use Backstage\Announcements\Resources\Announcements\Pages\CreateAnnouncement;
use Backstage\Announcements\Resources\Announcements\Pages\EditAnnouncement;
use Backstage\Announcements\Resources\Announcements\Pages\ListAnnouncements;
use Backstage\Announcements\Resources\Announcements\Pages\ViewAnnouncement;
use Backstage\Announcements\Resources\Announcements\Schemas\AnnouncementForm;
use Backstage\Announcements\Resources\Announcements\Schemas\AnnouncementInfolist;
use Backstage\Announcements\Resources\Announcements\Tables\AnnouncementsTable;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static bool $isScopedToTenant = false;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    protected static bool $shouldRegisterNavigation = true;

    public static function shouldRegisterNavigation(): bool
    {
        return static::$shouldRegisterNavigation;
    }

    public static function scopeToTenant(bool $scoped = true): void
    {
        static::$isScopedToTenant = $scoped;
    }

    public static function setShouldRegisterNavigation(bool $shouldRegister): void
    {
        static::$shouldRegisterNavigation = $shouldRegister;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return '/announcements';
    }

    public static function form(Schema $schema): Schema
    {
        return AnnouncementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AnnouncementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnnouncementsTable::configure($table);
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
            'index' => ListAnnouncements::route('/'),
            'create' => CreateAnnouncement::route('/create'),
            'view' => ViewAnnouncement::route('/{record}'),
            'edit' => EditAnnouncement::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
