<?php

namespace Backstage\Http\Middleware\Filament;

use Closure;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class RequireTwoFactorAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $user = Filament::auth()->user();
        $tenant = Filament::getTenant();

        if (! $user || ! $tenant || ! $tenant->two_factor_required) {
            return $next($request);
        }

        if (! $user->getAppAuthenticationSecret()) {
            Notification::make()
                ->title('Two-Factor Authentication Required')
                ->body('Please set up two-factor authentication to access this site.')
                ->warning()
                ->send();

            return redirect()->route('filament.backstage.auth.profile');
        }

        return $next($request);
    }
}
