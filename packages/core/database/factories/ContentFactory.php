<?php

namespace Backstage\Database\Factories;

use Backstage\Models\Content;
use Backstage\Models\Language;
use Backstage\Models\Site;
use Backstage\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentFactory extends Factory
{
    protected $model = Content::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_ulid' => Site::factory(),
            'language_code' => Language::factory(),
            'type_slug' => Type::factory(),
            'name' => 'Home',
            'slug' => 'home',
            'path' => '/',
            'meta_tags' => ['title' => 'Home'],
            'published_at' => now(),
            'edited_at' => now(),
            'public' => true,
        ];
    }
}
