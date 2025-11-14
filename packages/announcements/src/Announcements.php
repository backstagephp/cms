<?php

namespace Backstage\Announcements;

use Backstage\Announcements\Models\Announcement;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Pages\SimplePage;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class Announcements
{
    public function register()
    {
        if (! Schema::hasTable(app(Announcement::class)->getTable())) {
            return;
        }

        if (App::runningInConsole()) {
            return;
        }

        Announcement::query()
            ->get()
            ->each(function ($announcement) {
                if (! $announcement->isActive()) {
                    return;
                }

                collect($announcement->scopes)->each(function ($scope) use ($announcement) {
                    $instance = new $scope;

                    $hook = match (true) {
                        $instance instanceof SimplePage => PanelsRenderHook::SIMPLE_PAGE_START,
                        $instance instanceof Page => PanelsRenderHook::CONTENT_START,
                        default => null,
                    };

                    if ($hook) {
                        FilamentView::registerRenderHook(
                            name: $hook,
                            hook: function () use ($announcement, $scope) {
                                if ($announcement->isDismissedBy(Filament::auth()->user()?->id)) {
                                    return '';
                                }

                                return $announcement->render($scope);
                            },
                            scopes: $scope
                        );
                    }
                });
            });
    }
}
