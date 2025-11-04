<?php

namespace Backstage\Http\Middleware\Filament;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class HasTenant
{
    public function handle(Request $request, Closure $next)
    {
        if (Filament::auth()->check() && ! Filament::auth()->getUser()->sites()->first() && ! $request->routeIs('filament.backstage.tenant.registration')) {
            return redirect()->route('filament.backstage.tenant.registration');
        }

        return $next($request);
    }
}
