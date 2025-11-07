<?php

namespace Backstage\Fields\Fields;

use Backstage\Fields\Concerns\HasConfigurableFields;
use Backstage\Fields\Concerns\HasFieldTypeResolver;
use Backstage\Fields\Concerns\HasOptions;
use Backstage\Fields\Contracts\FieldContract;
use Backstage\Fields\Enums\Field as FieldEnum;
use Backstage\Fields\Facades\Fields;
use Backstage\Fields\Models\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater as Input;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Saade\FilamentAdjacencyList\Forms\Components\AdjacencyList;

class Repeater extends Base implements FieldContract
{
    use HasConfigurableFields;
    use HasFieldTypeResolver;
    use HasOptions;

    public function getFieldType(): ?string
    {
        return 'repeater';
    }

    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'addActionLabel' => __('Add row'),
            'addable' => true,
            'deletable' => true,
            'reorderable' => false,
            'reorderableWithButtons' => false,
            'collapsible' => false,
            'collapsed' => false,
            'cloneable' => false,
            'columns' => 1,
            'form' => [],
            'table' => false,
            'compact' => false,
        ];
    }

    public static function make(string $name, ?Field $field = null): Input
    {
        $input = self::applyDefaultSettings(Input::make($name), $field);

        $isReorderable = $field->config['reorderable'] ?? self::getDefaultConfig()['reorderable'];
        $isReorderableWithButtons = $field->config['reorderableWithButtons'] ?? self::getDefaultConfig()['reorderableWithButtons'];

        $input = $input->label($field->name ?? self::getDefaultConfig()['label'] ?? null)
            ->addActionLabel($field->config['addActionLabel'] ?? self::getDefaultConfig()['addActionLabel'])
            ->addable($field->config['addable'] ?? self::getDefaultConfig()['addable'])
            ->deletable($field->config['deletable'] ?? self::getDefaultConfig()['deletable'])
            ->reorderable($isReorderable)
            ->collapsible($field->config['collapsible'] ?? self::getDefaultConfig()['collapsible'])
            ->cloneable($field->config['cloneable'] ?? self::getDefaultConfig()['cloneable'])
            ->columns($field->config['columns'] ?? self::getDefaultConfig()['columns']);

        if ($field->config['compact'] ?? self::getDefaultConfig()['compact']) {
            $input = $input->compact();
        }

        if ($isReorderableWithButtons) {
            $input = $input->reorderableWithButtons();
        }

        // Fix for Filament Forms v4.2.0 reorder bug
        // The default reorder action has a bug where array_flip() creates integer values
        // that get merged with the state array, causing type errors
        if ($isReorderable || $isReorderableWithButtons) {
            $input = $input->reorderAction(function ($action) {
                return $action->action(function (array $arguments, Input $component): void {
                    $currentState = $component->getRawState();
                    $newOrder = $arguments['items'];

                    // Reorder the items based on the new order
                    $reorderedItems = [];
                    foreach ($newOrder as $oldIndex) {
                        if (isset($currentState[$oldIndex])) {
                            $reorderedItems[] = $currentState[$oldIndex];
                        }
                    }

                    $component->rawState($reorderedItems);
                    $component->callAfterStateUpdated();
                    $component->shouldPartiallyRenderAfterActionsCalled() ? $component->partiallyRender() : null;
                });
            });
        }

        if ($field && ! $field->relationLoaded('children')) {
            $field->load('children');
        }

        if ($field && $field->children->count() > 0) {
            $isTableMode = $field->config['table'] ?? self::getDefaultConfig()['table'];

            if ($isTableMode) {
                $input = $input
                    ->table(self::generateTableColumns($field->children))
                    ->schema(self::generateSchemaFromChildren($field->children, false));
            } else {
                $input = $input->schema(self::generateSchemaFromChildren($field->children, false));
            }
        }

        return $input;
    }

    public function getForm(): array
    {
        return [
            Tabs::make()
                ->schema([
                    Tab::make('General')
                        ->label(__('General'))
                        ->schema($this->getBaseFormSchema()),
                    Tab::make('Field specific')
                        ->label(__('Field specific'))
                        ->schema([
                            Grid::make(3)->schema([
                                Toggle::make('config.addable')
                                    ->label(__('Addable')),
                                Toggle::make('config.deletable')
                                    ->label(__('Deletable')),
                                Toggle::make('config.reorderable')
                                    ->label(__('Reorderable'))
                                    ->live(),
                                Toggle::make('config.reorderableWithButtons')
                                    ->label(__('Reorderable with buttons'))
                                    ->dehydrated()
                                    ->disabled(fn (Get $get): bool => $get('config.reorderable') === false),
                                Toggle::make('config.collapsible')
                                    ->label(__('Collapsible')),
                                Toggle::make('config.collapsed')
                                    ->label(__('Collapsed'))
                                    ->visible(fn (Get $get): bool => $get('config.collapsible') === true),
                                Toggle::make('config.cloneable')
                                    ->label(__('Cloneable')),
                            ]),
                            Grid::make(2)->schema([
                                TextInput::make('config.addActionLabel')
                                    ->label(__('Add action label')),
                                TextInput::make('config.columns')
                                    ->label(__('Columns'))
                                    ->default(1)
                                    ->numeric(),
                                Toggle::make('config.table')
                                    ->label(__('Table repeater')),
                                Toggle::make('config.compact')
                                    ->label(__('Compact table'))
                                    ->live()
                                    ->visible(fn (Get $get): bool => $get('config.table') === true),
                            ]),
                            AdjacencyList::make('config.form')
                                ->columnSpanFull()
                                ->label(__('Fields'))
                                ->orderColumn('position')
                                ->relationship('children')
                                ->live(debounce: 250)
                                ->labelKey('name')
                                ->indentable(false)
                                ->moveable(true)
                                ->reorderable(false)
                                ->addable(fn (string $operation) => $operation !== 'create')
                                ->disabled(fn (string $operation) => $operation === 'create')
                                ->hint(fn (string $operation) => $operation === 'create' ? __('Fields can be added once the field is created.') : '')
                                ->hintColor('primary')
                                ->schema([
                                    Section::make('Field')
                                        ->columns(3)
                                        ->schema([
                                            Hidden::make('model_type')
                                                ->default('field'),
                                            Hidden::make('model_key')
                                                ->default('ulid'),
                                            TextInput::make('name')
                                                ->label(__('Name'))
                                                ->required()
                                                ->placeholder(__('Name'))
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old, ?Field $record) {
                                                    if (! $record || blank($get('slug'))) {
                                                        $set('slug', Str::slug($state));
                                                    }

                                                    $currentSlug = $get('slug');

                                                    if (! $record?->slug && (! $currentSlug || $currentSlug === Str::slug($old))) {
                                                        $set('slug', Str::slug($state));
                                                    }
                                                }),
                                            TextInput::make('slug')
                                                ->readonly(),
                                            Select::make('field_type')
                                                ->searchable()
                                                ->preload()
                                                ->label(__('Field Type'))
                                                ->live(debounce: 250)
                                                ->reactive()
                                                ->default(FieldEnum::Text->value)
                                                ->options(
                                                    function () {
                                                        $options = array_merge(
                                                            FieldEnum::array(),
                                                            $this->prepareCustomFieldOptions(Fields::getFields())
                                                        );

                                                        asort($options);

                                                        return $options;
                                                    }
                                                )
                                                ->required()
                                                ->afterStateUpdated(function ($state, Set $set) {
                                                    $set('config', []);

                                                    $set('config', $this->initializeConfig($state));
                                                }),
                                        ])->columnSpanFull(),
                                    Section::make('Configuration')
                                        ->columns(3)
                                        ->schema(fn (Get $get) => $this->getFieldTypeFormSchema(
                                            $get('field_type')
                                        ))
                                        ->visible(fn (Get $get) => filled($get('field_type'))),
                                ]),
                        ]),
                ])->columnSpanFull(),
        ];
    }

    protected function excludeFromBaseSchema(): array
    {
        return ['defaultValue'];
    }

    private static function generateTableColumns(Collection $children): array
    {
        $columns = [];

        $children = $children->sortBy('position');

        foreach ($children as $child) {
            $columns[] = TableColumn::make($child['slug']);
        }

        return $columns;
    }

    private static function generateSchemaFromChildren(Collection $children, bool $isTableMode = false): array
    {
        $schema = [];

        $children = $children->sortBy('position');

        foreach ($children as $child) {
            $fieldType = $child['field_type'];

            $field = self::resolveFieldTypeClassName($fieldType);

            if ($field === null) {
                continue;
            }

            $schema[] = $field::make($child['slug'], $child);
        }

        return $schema;
    }
}
