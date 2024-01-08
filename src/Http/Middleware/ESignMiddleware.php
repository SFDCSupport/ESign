<?php

namespace NIIT\ESign\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ESignMiddleware
{
    public function handle(Request $request, Closure $next, ...$args)
    {
        return $next($request);
    }
}
