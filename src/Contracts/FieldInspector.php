<?php

namespace Backstage\Contracts;

interface FieldInspector
{
    /**
     * Initialize a default field type and return its details
     *
     * @return array{
     *   exists: bool,
     *   class: ?string,
     *   methods: array,
     *   properties: array,
     *   constants: array,
     *   interfaces: array,
     *   instance: ?object,
     *   parentClass: ?string,
     *   traits: array
     * }
     */
    public function initializeDefaultField(string $fieldType): array;

    /**
     * Initialize a custom field type and return its details
     *
     * @return array{
     *   exists: bool,
     *   class: ?string,
     *   methods: array,
     *   properties: array,
     *   constants: array,
     *   interfaces: array,
     *   instance: ?object,
     *   parentClass: ?string,
     *   traits: array
     * }
     */
    public function initializeCustomField(string $fieldType): array;
}
