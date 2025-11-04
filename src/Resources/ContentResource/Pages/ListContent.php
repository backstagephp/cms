<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Models\Type;
use Backstage\Resources\ContentResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListContent extends ListRecords
{
    protected static string $resource = ContentResource::class;

    public ?string $type = null;

    public ?string $show = null;

    protected $queryString = ['type', 'show'];

    protected function getHeaderActions(): array
    {
        $typeFilterActions = [];
        $typeCreateActions = [];
        foreach (Type::orderBy('name')->get() as $type) {
            $typeFilterActions[] = Action::make(__($type->name))
                ->url(fn (): string => route('filament.backstage.resources.content.index', ['type' => $type->slug, 'show' => 'database', 'tenant' => Filament::getTenant()]));
            $typeCreateActions[] = Action::make(__($type->name))
                ->url(fn (): string => route('filament.backstage.resources.content.create', ['type' => $type->slug, 'tenant' => Filament::getTenant()]));
        }

        return [
            Action::make(__('List'))
                ->url(fn (): string => route('filament.backstage.resources.content.index', ['tenant' => Filament::getTenant()]))
                ->icon('heroicon-o-bars-3')
                ->color($this->show != 'database' ? null : 'gray')
                ->iconPosition('before'),

            ActionGroup::make(
                $typeFilterActions
            )
                ->label(__('Database') . ($this->type ? ' (' . Type::where('slug', $this->type)->firstOrFail()->name . ')' : ''))
                ->dropdownPlacement('bottom-end')
                ->color($this->show == 'database' ? null : 'gray')
                ->icon('heroicon-o-circle-stack')
                ->iconPosition('before')
                ->grouped(false)
                ->button(),
            ActionGroup::make(
                $typeCreateActions
            )
                ->label(__('New Content'))
                ->dropdownPlacement('bottom-end')
                ->icon('heroicon-o-chevron-down')
                ->iconPosition('after')
                ->button(),
        ];
    }

    public function table(Table $table): Table
    {
        if ($this->type) {
            $table = ContentResource::tableDatabase($table, Type::where('slug', $this->type)->firstOrFail());
        } else {
            $table = ContentResource::table($table);
        }

        if ($this->shouldBeReorderable()) {
            return $table->reorderable('position');
        }

        return $table;
    }

    protected function handleTableFilterUpdates(): void
    {
        parent::handleTableFilterUpdates();

        $this->table = $this->table($this->makeTable());
    }

    protected function shouldBeReorderable(): bool
    {
        return isset($this->filters['parent_ulid']['value']) &&
            $this->filters['parent_ulid']['value'] === '0';
    }
}
