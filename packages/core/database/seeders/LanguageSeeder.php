<?php

namespace Backstage\Database\Seeders;

use Backstage\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::factory()->state([
            'code' => 'nl-NL',
            'name' => 'Dutch',
            'native' => 'Nederlands',
            'default' => true,
        ])->create();

        Language::factory()->state([
            'code' => 'nl-BE',
            'name' => 'Dutch',
            'native' => 'Nederlands',
            'default' => false,
        ])->create();

        Language::factory()->state([
            'code' => 'fr-BE',
            'name' => 'French',
            'native' => 'Français',
            'default' => false,
        ])->create();

        Language::factory()->state([
            'code' => 'en',
            'name' => 'Engels',
            'native' => 'English',
            'default' => false,
        ])->create();

        Language::factory()->state([
            'code' => 'de',
            'name' => 'German',
            'native' => 'Deutsch',
            'default' => false,
        ])->create();
    }
}
