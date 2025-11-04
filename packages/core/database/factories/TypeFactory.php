<?php

namespace Backstage\Database\Factories;

use Backstage\Models\Site;
use Backstage\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeFactory extends Factory
{
    protected $model = Type::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'slug' => $this->faker->slug,
            'name_plural' => $this->faker->word,
            'icon' => 'document',
            'name_field' => 'name',
            'body_field' => 'body',
            'public' => true,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Type $type) {
            $type->sites()->attach(Site::default());
        });
    }
}
