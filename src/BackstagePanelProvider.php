<?php

namespace Backstage;

use Backstage\Http\Middleware\Filament\HasTenant;
use Backstage\Http\Middleware\Filament\RequireTwoFactorAuthentication;
use Backstage\Http\Middleware\Filament\ScopedBySite;
use Backstage\Models\Site;
use Backstage\Resources\SiteResource\RegisterSite;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class BackstagePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $this->configureThemeScripts();

        $panel = $this->configureBasicSettings($panel);
        $panel = $this->configureTheming($panel);
        $panel = $this->configureAuthentication($panel);
        $panel = $this->configureNavigation($panel);
        $panel = $this->configureTenancy($panel);

        return $panel;
    }

    protected function configureThemeScripts(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_BEFORE,
            fn (): string => Blade::render(
                <<<'HTML'
                <script>
                document.addEventListener('livewire:navigated', () => {
                    if(window.matchMedia('(prefers-color-scheme: dark)').matches || localStorage.getItem('theme') === 'dark') {
                        document.getElementsByTagName('html')[0].style.backgroundColor = '#030712';

                        var meta = document.createElement('meta');
                        meta.setAttribute('name', 'background-color');
                        meta.setAttribute('content', '#030712');
                        document.getElementsByTagName('head')[0].appendChild(meta);

                        var meta = document.createElement('meta');
                        meta.setAttribute('name', 'theme-color');
                        meta.setAttribute('content', '#030712');
                        document.getElementsByTagName('head')[0].appendChild(meta);
                    }
                    else {
                        document.getElementsByTagName('html')[0].style.backgroundColor = '#ffffff';

                        var meta = document.createElement('meta');
                        meta.setAttribute('name', 'background-color');
                        meta.setAttribute('content', '#ffffff');
                        document.getElementsByTagName('head')[0].appendChild(meta);

                        var meta = document.createElement('meta');
                        meta.setAttribute('name', 'theme-color');
                        meta.setAttribute('content', '#ffffff');
                        document.getElementsByTagName('head')[0].appendChild(meta);
                    }
                });
                </script>
                HTML
            ),
        );
    }

    protected function configureBasicSettings(Panel $panel): Panel
    {
        return $panel
            ->id('backstage')
            ->path('backstage')
            ->databaseNotifications()
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->emailChangeVerification()
            ->profile()
            ->sidebarCollapsibleOnDesktop()
            ->multiFactorAuthentication([
                AppAuthentication::make()
                    ->recoverable(),
            ])
            ->unsavedChangesAlerts()
            ->default(config('backstage.cms.panel.default', true))
            ->plugins(config('backstage.cms.panel.plugins', []))
            ->resources(config('backstage.cms.panel.resources', []))
            ->widgets(config('backstage.cms.panel.widgets', []))
            ->pages(config('backstage.cms.panel.pages', []))
            ->defaultAvatarProvider(BackstageAvatarProvider::class);
    }

    protected function configureTheming(Panel $panel): Panel
    {
        return $panel
            ->colors(fn () => [
                'primary' => Color::generateV3Palette(Site::default()?->primary_color ?: '#ff9900'),
            ])
            ->brandLogo(function () {
                if (Filament::getTenant() && Filament::getTenant()->logo) {
                    return asset(Filament::getTenant()->logo);
                }

                return '';
            });
    }

    protected function configureAuthentication(Panel $panel): Panel
    {
        return $panel
            ->login()
            ->passwordReset()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                HasTenant::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    protected function configureNavigation(Panel $panel): Panel
    {
        return $panel
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Content'),
                NavigationGroup::make()
                    ->label('Structure'),
                NavigationGroup::make()
                    ->label('Users'),
                NavigationGroup::make()
                    ->label('Manage'),
            ]);
    }

    protected function configureTenancy(Panel $panel): Panel
    {
        return $panel
            ->tenant(Site::class)
            ->tenantRegistration(RegisterSite::class)
            ->tenantMiddleware([
                ScopedBySite::class,
                RequireTwoFactorAuthentication::class,
            ]);
    }
}
