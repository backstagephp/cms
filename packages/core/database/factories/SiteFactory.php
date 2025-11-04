<?php

namespace Backstage\Database\Factories;

use Backstage\Models\Domain;
use Backstage\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $name = fake()->name(),
            'slug' => Str::slug($name),
            'title' => $this->faker->sentence(),
            'title_separator' => '|',
            'primary_color' => $this->faker->hexColor(),
            'timezone' => $this->faker->timezone(),
            'path' => '',
            'auth' => false,
            'default' => false,
            'trailing_slash' => false,
        ];
    }

    public function withDomain($domain = 'localhost', $language = null): self
    {
        return $this->has(Domain::factory()
            ->withLanguage($language)
            ->state([
                'name' => $domain,
            ]));
    }
}
