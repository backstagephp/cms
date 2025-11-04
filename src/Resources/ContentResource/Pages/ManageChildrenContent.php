<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Resources\ContentResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ManageChildrenContent extends ManageRelatedRecords
{
    protected static string $resource = ContentResource::class;

    protected static string $relationship = 'children';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-duplicate';

    public function getTitle(): string | Htmlable
    {
        $recordTitle = $this->getRecordTitle();

        $recordTitle = $recordTitle instanceof Htmlable ? $recordTitle->toHtml() : $recordTitle;

        return __('Manage :resource related content', [
            'resource' => $recordTitle,
        ]);
    }

    public function getBreadcrumb(): string
    {
        return __('Related Content');
    }

    public static function getNavigationLabel(): string
    {
        return __('Related Content');
    }

    public function getModelLabel(): string
    {
        return __('Related Content');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Related Content');
    }

    public function form(Schema $schema): Schema
    {
        return ContentResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ContentResource::table($table)->reorderable('position');
    }
}
