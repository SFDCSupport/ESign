<?php

namespace NIIT\ESign\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SigningMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        abort_if(! $request->ajax() || ! $request->headers->contains('X-ESign'), 403, 'Forbidden');

        return $next($request);
    }
}
