<?php

namespace Backstage;

use Filament\Contracts\Plugin;
use Filament\Panel;

class BackstagePlugin implements Plugin
{
    public function getId(): string
    {
        return 'backstage';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
