<?php

namespace Backstage\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ContentPathCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        // Slugify $value by - but dont slugify "/"
        $value = collect(explode('/', $value))->map(fn ($part) => Str::slug($part, '-'))->implode('/');

        return ltrim($value, '/') ?: '/';
    }
}
