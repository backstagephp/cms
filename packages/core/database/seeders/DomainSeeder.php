<?php

namespace Backstage\Database\Seeders;

use Backstage\Models\Domain;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Domain::factory()->create();
    }
}
