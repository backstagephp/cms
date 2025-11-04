<?php

namespace Backstage\Database\Factories;

use Backstage\Models\Form;
use Backstage\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFactory extends Factory
{
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_ulid' => Site::factory(),
            'name' => 'Contact',
            'submit_button' => 'Send',
        ];
    }
}
