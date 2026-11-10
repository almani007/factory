<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Ensures API requests are treated as JSON requests so that an unauthenticated
 * call returns a 401 JSON response instead of being redirected to a login page.
 */
class EnsureApiJson
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
