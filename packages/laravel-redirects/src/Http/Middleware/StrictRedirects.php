<?php

namespace Backstage\Redirects\Laravel\Http\Middleware;

use Backstage\Redirects\Laravel\Http\Middleware\Concerns\SkipMethod;
use Backstage\Redirects\Laravel\Models\Redirect;
use Closure;
use Illuminate\Http\Request;

class StrictRedirects
{
    use SkipMethod;

    public function handleNonPost(Request $request, Closure $next)
    {
        /**
         * @var \Backstage\Redirects\Laravel\Models\Redirect|null $checker
         */
        $checker = Redirect::all()
            ->firstWhere(function (Redirect $redirect) use ($request) {
                return str($request->url())
                    ->replace(['http://', 'https://'], '')
                    ->replace(['www.'], '')
                    ->exactly(
                        str($redirect->source)
                            ->replace(['http://', 'https://'], '')
                            ->replace(['www.'], '')
                    );
            });

        if (! $checker) {
            return $next($request);
        }

        return $checker->redirect($request);
    }
}
