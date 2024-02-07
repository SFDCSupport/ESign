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
        $loadedModel = $request->signer()->loadMissing('document');
        $notShowRoute = ! $request->routeIs('esign.signing.show');
        $documentStatus = $loadedModel->document->status;

        abort_if(
            ! $signer ||
            $documentStatus === DocumentStatus::DRAFT ||
            ($notShowRoute && $documentStatus !== DocumentStatus::IN_PROGRESS),
            404
        );

        abort_if(
            $request->expectsJson() && ! $request->headers->has('X-ESign'),
            403,
            'Forbidden'
        );

        if (
            $signer->signing_status === SigningStatus::SIGNED && $notShowRoute
        ) {
            return redirect()->route('esign.signing.show', [
                'signing_url' => $signer->url,
            ]);
            //            return $disk->download(
            //                $signedDocument
            //            );
        }

        return $next($request);
    }
}
