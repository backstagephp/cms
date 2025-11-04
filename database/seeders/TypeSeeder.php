<?php

namespace Backstage\Database\Seeders;

use Backstage\Fields\Models\Field;
use Backstage\Models\Type;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Type::factory()->state([
            'name' => $name = 'Page',
            'name_plural' => Str::plural($name),
            'slug' => Str::slug($name),
            'icon' => 'document',
            'name_field' => 'title',
            'body_field' => 'body',
            'public' => true,
        ])
            ->has(Field::factory(1, [
                'name' => 'Title',
                'slug' => 'title',
                'field_type' => 'text',
                'position' => 1,
            ]))
            ->has(Field::factory(1, [
                'name' => 'Body',
                'slug' => 'body',
                'field_type' => 'rich-editor',
                'position' => 2,
            ]))
            ->has(Field::factory(1, [
                'name' => 'Blocks',
                'slug' => 'blocks',
                'field_type' => 'builder',
                'position' => 3,
            ]))
            ->create();
    }
}
