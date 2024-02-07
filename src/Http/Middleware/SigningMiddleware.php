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
        $document = $loadedModel->document;

        abort_if(
            ! $signer ||
            $document->statusIs(DocumentStatus::DRAFT) ||
            ($notShowRoute && $document->statusIsNot(DocumentStatus::IN_PROGRESS)),
            404
        );

        $isSigned = ($signer->signingStatusIs(SigningStatus::SIGNED));

        if (! $notShowRoute && ! $isSigned) {
            return redirect()->route('esign.signing.index', [
                'signing_url' => $signer->url,
            ]);
        }

        abort_if(
            $request->expectsJson() && ! $request->headers->has('X-ESign'),
            403,
            'Forbidden'
        );

        if (
            $signer->signingStatusIs(SigningStatus::SIGNED) && $notShowRoute
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
