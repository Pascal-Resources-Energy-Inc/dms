<?php

namespace App\Http\Middleware;

use Closure;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (! $request->user() || ! $request->user()->hasRole($roles)) {
            abort(403, 'You do not have permission to open this dashboard.');
        }

        return $next($request);
    }
}
