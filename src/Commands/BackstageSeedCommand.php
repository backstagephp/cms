<?php

namespace Backstage\Commands;

use Illuminate\Console\Command;

class BackstageSeedCommand extends Command
{
    public $signature = 'backstage:seed {--force}';

    public $description = 'Seed database with sample Backstage data';

    public function handle(): int
    {
        if (
            $this->option('force') ||
            $this->confirm('Do you really want to seed the database with sample Backstage data?')
        ) {
            $this->call('db:seed', ['--class' => 'Backstage\Database\Seeders\BackstageSeeder']);
        }

        return self::SUCCESS;
    }
}
