<?php

namespace NIIT\ESign\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ESignMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (class_exists('\NIIT\Survey\Http\Middlewares\SurveyMiddleware')) {
            //return (new \NIIT\Survey\Http\Middlewares\SurveyMiddleware())->handle($request, $next);
        }

        return $next($request);
    }
}
