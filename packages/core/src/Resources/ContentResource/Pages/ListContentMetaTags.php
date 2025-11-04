<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Models\Content;
use Backstage\Models\Type;
use Backstage\Resources\ContentResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Builder;

class ListContentMetaTags extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        $types = Type::orderBy('name')->get();
        $actions = [];
        foreach ($types as $type) {
            $actions[] = Action::make($type->name)
                // ->icon($type->icon)
                ->url(static::getResource()::getUrl('meta_tags', ['filters' => ['type_slug' => ['values' => [$type->slug]]]]))
                ->color('primary')
                ->label($type->name);
        }

        return [ActionGroup::make($actions)];
    }

    public function table(Table $table): Table
    {
        return static::getResource()::table($table)
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('public', true);
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                TextInputColumn::make('meta_tags.title')
                    ->label('Page title')
                    ->searchable()
                    ->sortable(),
                TextInputColumn::make('meta_tags.description')
                    ->label('Meta description')
                    ->searchable()
                    ->sortable(),
                TextInputColumn::make('meta_tags.keywords')
                    ->label('Meta keywords')
                    ->state(function (Content $record): string {
                        return implode(', ', $record->meta_tags['keywords'] ?? []);
                    })
                    ->updateStateUsing(function ($record, $state) {
                        $record->update(['meta_tags->keywords' => array_filter(array_map('trim', explode(',', $state)))]);
                    })
                    ->searchable()
                    ->sortable(),
            ]);
    }
}
