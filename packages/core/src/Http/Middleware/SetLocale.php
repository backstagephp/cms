<?php

namespace Backstage\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (null !== $content = $request->content()) {
            App::setLocale($content->language->code);
        }

        return $next($request);
    }
}
