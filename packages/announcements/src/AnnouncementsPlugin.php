<?php

namespace Backstage\Announcements;

use Backstage\Announcements\Resources\Announcements\AnnouncementResource;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;

class AnnouncementsPlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool $shouldRegisterNavigation = true;

    protected array | \Closure | null $forcedScopes = null;

    public function getId(): string
    {
        return 'backstage-announcements';
    }

    public function register(Panel $panel): void
    {
        // Set navigation registration before registering the resource
        AnnouncementResource::setShouldRegisterNavigation($this->shouldRegisterNavigation);
        AnnouncementResource::scopeToTenant(false);

        $panel->resources([
            AnnouncementResource::class,
        ]);

        \Backstage\Announcements\Facades\Announcements::register();
    }

    public function boot(Panel $panel): void {}

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

    public function canRegisterNavigation(bool $canRegister = true): static
    {
        $this->shouldRegisterNavigation = $canRegister;

        return $this;
    }

    public function forceScopes(array | \Closure $scopes): static
    {
        $this->forcedScopes = $scopes;

        return $this;
    }

    public function getForcedScopes(): ?array
    {
        return $this->evaluate($this->forcedScopes);
    }
}
