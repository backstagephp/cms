<?php

namespace Backstage\CustomFields;

use Backstage\Fields\Fields\Select as Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Select extends Base
{
    public static function mutateFormDataCallback(Model $record, mixed $field, array $data): array
    {
        if (! property_exists($record, 'valueColumn') || ! isset($record->values[$field->ulid])) {
            return $data;
        }

        $value = $record->values[$field->ulid];
        $data[$record->valueColumn][$field->ulid] = self::normalizeSelectValue($value, $field);

        return $data;
    }

    public static function mutateBeforeSaveCallback(Model $record, mixed $field, array $data): array
    {
        if (! isset($field->config['optionType']) || ! collect($field->config['optionType'])->contains('relationship') || empty($field->config['relations'])) {
            return $data;
        }

        DB::table('relationables')
            ->where('relation_type', $record->getMorphClass())
            ->where('relation_ulid', $record->ulid)
            ->delete();

        $values = $data['values'][$field->ulid];
        if (! is_array($values)) {
            $values = [$values];
        }

        foreach ($field->config['relations'] as $relation) {
            $resource = $relation['resource'];
            $key = $relation['relationKey'];

            /** @phpstan-ignore-next-line */
            $instance = new static;
            $model = $instance->resolveResourceModel($resource);

            $results = $model->whereIn($key, $values)->get();

            foreach ($results as $result) {
                DB::table('relationables')->insert([
                    'relation_type' => $record->getMorphClass(),
                    'relation_ulid' => $record->ulid,
                    'related_type' => $resource,
                    'related_ulid' => $result->ulid,
                ]);
            }
        }

        return $data;
    }
}
