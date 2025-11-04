<?php

namespace Backstage\CustomFields;

use Backstage\Contracts\FieldContract;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Fields\Fields\Base;
use Backstage\Fields\Models\Field;
use Backstage\Models\Block;
use Filament\Forms\Components\Builder as Input;
use Filament\Forms\Components\Builder\Block as BuilderBlock;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Collection;

class Builder extends Base implements FieldContract
{
    use CanMapDynamicFields {
        resolveFormFields as private traitResolveFormFields;
        resolveCustomFields as private traitResolveCustomFields;
        resolveFieldInput as private traitResolveFieldInput;
    }

    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
        ];
    }

    public static function make(string $name, ?Field $field = null): Input
    {
        $input = self::applyDefaultSettings(
            Input::make($name)
                ->label($field->name)
                ->collapsed(true)
                ->collapsible()
                ->blocks(
                    self::getBlockOptions()
                ),
            $field
        );

        return $input;
    }

    /**
     * Get the blocks
     */
    private static function getBlockOptions()
    {
        $blocks = Block::with('fields')->get();
        $options = [];

        foreach ($blocks as $block) {
            $options[] = BuilderBlock::make($block->slug)
                ->label($block->name)
                ->icon($block->icon ? 'heroicon-o-' . $block->icon : null)
                ->schema(
                    self::resolveFormFields(record: $block, isNested: true)
                );
        }

        return $options;
    }

    public function getForm(): array
    {
        return [
            Tabs::make()
                ->schema([
                    Tab::make('General')
                        ->label(__('General'))
                        ->schema([
                            ...parent::getForm(),
                        ]),
                ])->columnSpanFull(),
        ];
    }

    private static function resolveFormFields(mixed $record = null, bool $isNested = false): array
    {
        $instance = new self;

        $isNested = true; // Builder fields are always nested

        return $instance->traitResolveFormFields(record: $record, isNested: $isNested);
    }

    private static function resolveCustomFields(): Collection
    {
        $instance = new self;

        return $instance->traitResolveCustomFields();
    }

    private static function resolveFieldInput(Field $field, Collection $customFields, mixed $record = null, bool $isNested = false): ?object
    {
        $instance = new self;

        $isNested = true; // Builder fields are always nested

        return $instance->traitResolveFieldInput(field: $field, customFields: $customFields, record: $record, isNested: $isNested);
    }
}
