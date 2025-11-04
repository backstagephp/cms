<?php

namespace Backstage\Tests;

use Backstage\BackstageServiceProvider;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

#[WithMigration]
class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            BackstageServiceProvider::class,
        ];
    }

    public function defineEnvironment($app)
    {

        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        foreach (glob(__DIR__ . '/../config/*.php') as $filename) {
            $app['config']->set(pathinfo($filename)['filename'], require $filename);
        }

    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('vendor:publish', ['--tag' => 'backstage-config', '--force' => true]);
        $this->artisan('vendor:publish', ['--tag' => 'backstage-migrations', '--force' => true]);
    }

    public function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function refreshApplication()
    {
        parent::refreshApplication();

        Model::shouldBeStrict();
    }

    public function getEnvironmentSetUp($app)
    {
        // config()->set('database.default', 'sqlite');
    }
}
