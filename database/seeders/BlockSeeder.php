<?php

namespace Backstage\Database\Seeders;

use Backstage\Fields\Models\Field;
use Backstage\Models\Block;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Block::factory()->state([
            'name' => $name = 'Text',
            'slug' => Str::slug($name),
            'icon' => 'document-text',
            'name_field' => null,
        ])
            ->has(Field::factory(1, [
                'name' => 'Body',
                'slug' => 'body',
                'field_type' => 'rich-editor',
                'position' => 1,
            ]))
            ->create();

        Block::factory()->state([
            'name' => $name = 'Form',
            'slug' => Str::slug($name),
            'icon' => 'document-text',
            'name_field' => null,
            'component' => 'form',
        ])
            ->has(Field::factory(1, [
                'name' => 'Slug',
                'slug' => 'slug',
                'field_type' => 'text',
                'position' => 1,
            ]))
            ->create();
    }
}
