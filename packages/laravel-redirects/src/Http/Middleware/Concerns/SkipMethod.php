<?php

namespace Backstage\Redirects\Laravel\Http\Middleware\Concerns;

use Closure;
use Illuminate\Http\Request;

trait SkipMethod
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        return $this->handleNonPost($request, $next);
    }

    /**
     * Handle non-POST requests.
     */
    abstract protected function handleNonPost(Request $request, Closure $next);
}
