<?php

namespace NIIT\ESign\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\SigningStatus;

class SigningMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $signer = $request->signer();

        abort_if(! $signer, 404);

        $loadedModel = $request->signer()->loadMissing('document');

        //        abort_if(
        //            $loadedModel->document->status !== DocumentStatus::ACTIVE || $signer->signing_status !== SigningStatus::NOT_SIGNED,
        //            404,
        //        );

        abort_if(
            $request->expectsJson() && ! $request->headers->has('X-ESign'),
            403,
            'Forbidden'
        );

        return $next($request);
    }
}
