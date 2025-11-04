<?php

namespace Backstage\Database\Factories;

use Backstage\Models\FormAction;
use Backstage\Models\Language;
use Backstage\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormActionFactory extends Factory
{
    protected $model = FormAction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'language_code' => Language::factory(),
            'site_ulid' => Site::factory(),
            'type' => 'email',
            'config' => [
                'subject' => 'Contact submission',
                'to_email' => 'email',
                'to_name' => 'name',
                'body' => 'Thanks for your submission.',
            ],
        ];
    }
}
