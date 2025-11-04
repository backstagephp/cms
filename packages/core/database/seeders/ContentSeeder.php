<?php

namespace Backstage\Database\Seeders;

use Backstage\Fields\Models\Field;
use Backstage\Models\Content;
use Backstage\Models\ContentFieldValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Home
        Content::factory()
            ->state([])
            ->has(ContentFieldValue::factory(1, [
                'field_ulid' => Field::where('slug', 'title')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
                'value' => 'Home',
            ]), 'values')
            ->create();

        // Contact
        Content::factory()
            ->state([
                'name' => 'Contact',
                'slug' => 'contact',
                'path' => 'contact',
                'meta_tags' => ['title' => 'Contact'],
            ])
            ->has(ContentFieldValue::factory(1, [
                'field_ulid' => Field::where('slug', 'title')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
                'value' => 'Contact',
            ]), 'values')
            ->has(ContentFieldValue::factory(1, [
                'field_ulid' => Field::where('slug', 'blocks')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
                'value' => json_encode([
                    (string) Str::uuid() => ['type' => 'form', 'data' => ['slug' => 'contact']],
                ]),
            ]), 'values')->create();
    }
}
