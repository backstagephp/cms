<?php

namespace Backstage\Fields\Concerns;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait HasSelectableValues
{
    protected static function resolveResourceModel(string $tableName): ?object
    {
        $resources = config('backstage.fields.selectable_resources');
        $resourceClass = collect($resources)->first(function ($resource) use ($tableName) {
            $res = new $resource;
            $model = $res->getModel();
            $model = new $model;

            return $model->getTable() === $tableName;
        });

        if (! $resourceClass) {
            return null;
        }

        $resource = new $resourceClass;
        $model = $resource->getModel();

        return new $model;
    }

    protected static function addValuesToInput(mixed $input, mixed $field, string $type, string $method): mixed
    {
        // Ensure field config is properly initialized
        if (! static::ensureFieldConfig($field, $type)) {
            return $input;
        }

        $allOptions = [];

        // Handle relationship options
        if (static::shouldHandleRelationshipOptions($field, $type)) {
            $relationshipOptions = static::buildRelationshipOptions($field);
            $allOptions = static::mergeRelationshipOptions($allOptions, $relationshipOptions, $field, $type);
        }

        // Handle array options
        if (static::shouldHandleArrayOptions($field, $type)) {
            $allOptions = static::mergeArrayOptions($allOptions, $field, $type);
        }

        // Apply all merged options to the input
        if (! empty($allOptions)) {
            $input->$method($allOptions);
        }

        return $input;
    }

    protected static function ensureFieldConfig(mixed $field, string $type): bool
    {
        // Ensure field config exists and is an array
        if (! isset($field->config) || ! is_array($field->config)) {
            return false;
        }

        // Ensure the type key exists in the config to prevent undefined array key errors
        if (! array_key_exists($type, $field->config)) {
            $config = $field->config ?? [];
            $config[$type] = null;
            $field->config = $config;
        }

        return true;
    }

    protected static function shouldHandleRelationshipOptions(mixed $field, string $type): bool
    {
        return isset($field->config[$type]) &&
            (is_string($field->config[$type]) && $field->config[$type] === 'relationship') ||
            (is_array($field->config[$type]) && in_array('relationship', $field->config[$type]));
    }

    protected static function shouldHandleArrayOptions(mixed $field, string $type): bool
    {
        return isset($field->config[$type]) &&
            (is_string($field->config[$type]) && $field->config[$type] === 'array') ||
            (is_array($field->config[$type]) && in_array('array', $field->config[$type]));
    }

    protected static function buildRelationshipOptions(mixed $field): array
    {
        $relationshipOptions = [];

        foreach ($field->config['relations'] ?? [] as $relation) {
            if (! isset($relation['resource'])) {
                continue;
            }

            $model = static::resolveResourceModel($relation['resource']);

            if (! $model) {
                continue;
            }

            $query = $model::query();

            // Apply filters if they exist
            if (isset($relation['relationValue_filters'])) {
                foreach ($relation['relationValue_filters'] as $filter) {
                    if (isset($filter['column'], $filter['operator'], $filter['value'])) {
                        if (preg_match('/{session\.([^\}]+)}/', $filter['value'], $matches)) {
                            $sessionValue = session($matches[1]);
                            $filter['value'] = str_replace($matches[0], $sessionValue, $filter['value']);
                        }
                        $query->where($filter['column'], $filter['operator'], $filter['value']);
                    }
                }
            }

            $results = $query->get();

            if ($results->isEmpty()) {
                continue;
            }

            // Fallback to model's primary key for existing records that don't have relationKey set
            $relationKey = $relation['relationKey'] ?? $model->getKeyName();
            $opts = $results->pluck($relation['relationValue'] ?? 'name', $relationKey)->toArray();

            if (count($opts) === 0) {
                continue;
            }

            // Group by resource name
            $resourceName = Str::title($relation['resource']);
            $relationshipOptions[$resourceName] = $opts;
        }

        return $relationshipOptions;
    }

    protected static function mergeRelationshipOptions(array $allOptions, array $relationshipOptions, mixed $field, string $type): array
    {
        if (empty($relationshipOptions)) {
            return $allOptions;
        }

        // If both types are selected, group relationship options by resource
        if (isset($field->config[$type]) &&
            (is_array($field->config[$type]) && in_array('array', $field->config[$type]))) {
            return array_merge($allOptions, $relationshipOptions);
        } else {
            // For single relationship type, merge all options without grouping
            return array_merge($allOptions, ...array_values($relationshipOptions));
        }
    }

    protected static function mergeArrayOptions(array $allOptions, mixed $field, string $type): array
    {
        if (! isset($field->config['options']) || ! is_array($field->config['options'])) {
            return $allOptions;
        }

        // If both types are selected, group array options
        if (isset($field->config[$type]) &&
            (is_array($field->config[$type]) && in_array('relationship', $field->config[$type]))) {
            $allOptions[__('Custom Options')] = $field->config['options'];
        } else {
            // Use + operator instead of array_merge to preserve numeric string keys
            $allOptions = $allOptions + $field->config['options'];
        }

        return $allOptions;
    }

    protected static function getSelectableValuesConfig(): array
    {
        return [
            'options' => [],
            'descriptions' => [],
            'relations' => [],
            'contentType' => null,
            'relationKey' => null,
            'relationValue' => null,
        ];
    }

    protected function selectableValuesFormFields(string $type, string $label, string $arrayComponent): Fieldset
    {
        return Fieldset::make($label)
            ->columnSpanFull()
            ->label(__($label))
            ->schema([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        CheckboxList::make("config.{$type}")
                            ->options([
                                'array' => __('Array'),
                                'relationship' => __('Relationship'),
                            ])
                            ->afterStateHydrated(function (Get $get, Set $set) use ($type) {
                                $value = $get("config.{$type}");

                                // Set correct config value when creating records
                                $set("config.{$type}", is_array($value) ? $value : (is_bool($value) ? [] : [$value]));
                            })
                            ->label(__('Type'))
                            ->in(['array', 'relationship', ''])
                            ->nullable()
                            ->live(),
                        // Array options
                        $arrayComponent::make('config.options')
                            ->label(__('Options'))
                            ->columnSpanFull()
                            ->visible(
                                fn (Get $get): bool => is_array($get("config.{$type}")) && in_array('array', $get("config.{$type}")) ||
                                $get("config.{$type}") === 'array'
                            )
                            ->required(
                                fn (Get $get): bool => is_array($get("config.{$type}")) && in_array('array', $get("config.{$type}")) ||
                                $get("config.{$type}") === 'array'
                            ),
                        // Relationship options
                        Repeater::make('config.relations')
                            ->label(__('Relations'))
                            ->schema([
                                Grid::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('resource')
                                            ->label(__('Resource'))
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull()
                                            ->live(debounce: 250)
                                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                                $model = static::resolveResourceModel($state);

                                                if (! $model) {
                                                    return;
                                                }

                                                // Get all column names from the table
                                                $columns = Schema::getColumnListing($model->getTable());

                                                // Create options array with column names
                                                $columnOptions = collect($columns)->mapWithKeys(function ($column) {
                                                    return [$column => Str::title($column)];
                                                })->toArray();

                                                // Get the primary key of the model
                                                $primaryKey = $model->getKeyName();

                                                $set('relationValue', null);
                                                $set('relationValue_options', $columnOptions);
                                                $set('relationKey_options', $columnOptions);
                                                $set('relationKey', $primaryKey);
                                            })
                                            ->options(function () {
                                                $resources = config('backstage.fields.selectable_resources');

                                                return collect($resources)->map(function ($resource) {
                                                    $res = new $resource;
                                                    $model = $res->getModel();
                                                    $model = new $model;

                                                    return [
                                                        $model->getTable() => Str::title($model->getTable()),
                                                    ];
                                                })
                                                    ->collapse()
                                                    ->toArray();
                                            })
                                            ->noSearchResultsMessage(__('No types found'))
                                            ->required(
                                                fn (Get $get): bool => is_array($get("../../config.{$type}")) && in_array('relationship', $get("../../config.{$type}")) ||
                                                $get("../../config.{$type}") === 'relationship'
                                            ),
                                        Select::make('relationKey')
                                            ->label(__('Key Column'))
                                            ->helperText(__('The column to use as the unique identifier/value for each option'))
                                            ->options(function (Get $get) {
                                                $resource = $get('resource');
                                                if (! $resource) {
                                                    return [];
                                                }

                                                $model = static::resolveResourceModel($resource);
                                                if (! $model) {
                                                    return [];
                                                }

                                                $columns = Schema::getColumnListing($model->getTable());

                                                return collect($columns)->mapWithKeys(function ($column) {
                                                    return [$column => Str::title($column)];
                                                })->toArray();
                                            })
                                            ->searchable()
                                            ->visible(fn (Get $get): bool => ! empty($get('resource')))
                                            ->required(
                                                fn (Get $get): bool => is_array($get("../../config.{$type}")) && in_array('relationship', $get("../../config.{$type}")) ||
                                                $get("../../config.{$type}") === 'relationship'
                                            ),
                                        Select::make('relationValue')
                                            ->label(__('Display Column'))
                                            ->helperText(__('The column to use as the display text/label for each option'))
                                            ->options(function (Get $get) {
                                                $resource = $get('resource');
                                                if (! $resource) {
                                                    return [];
                                                }

                                                $model = static::resolveResourceModel($resource);
                                                if (! $model) {
                                                    return [];
                                                }

                                                $columns = Schema::getColumnListing($model->getTable());

                                                return collect($columns)->mapWithKeys(function ($column) {
                                                    return [$column => Str::title($column)];
                                                })->toArray();
                                            })
                                            ->searchable()
                                            ->visible(fn (Get $get): bool => ! empty($get('resource')))
                                            ->required(fn (Get $get): bool => ! empty($get('resource'))),
                                        Repeater::make('relationValue_filters')
                                            ->label(__('Filters'))
                                            ->visible(fn (Get $get): bool => ! empty($get('resource')))
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('column')
                                                            ->options(fn (Get $get) => $get('../../relationValue_options') ?? [
                                                                'slug' => __('Slug'),
                                                                'name' => __('Name'),
                                                            ])
                                                            ->live()
                                                            ->label(__('Column')),
                                                        Select::make('operator')
                                                            ->options([
                                                                '=' => __('Equal'),
                                                                '!=' => __('Not equal'),
                                                                '>' => __('Greater than'),
                                                                '<' => __('Less than'),
                                                                '>=' => __('Greater than or equal to'),
                                                                '<=' => __('Less than or equal to'),
                                                                'LIKE' => __('Like'),
                                                                'NOT LIKE' => __('Not like'),
                                                            ])
                                                            ->label(__('Operator')),
                                                        TextInput::make('value')
                                                            ->datalist(function (Get $get) {
                                                                $resource = $get('../../resource');
                                                                $column = $get('column');

                                                                if (! $resource || ! $column) {
                                                                    return [];
                                                                }

                                                                $model = static::resolveResourceModel($resource);

                                                                if (! $model) {
                                                                    return [];
                                                                }

                                                                return $model::query()
                                                                    ->select($column)
                                                                    ->distinct()
                                                                    ->pluck($column)
                                                                    ->toArray();
                                                            })
                                                            ->label(__('Value')),
                                                    ]),
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->visible(
                                fn (Get $get): bool => is_array($get("config.{$type}")) && in_array('relationship', $get("config.{$type}")) ||
                                $get("config.{$type}") === 'relationship'
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
