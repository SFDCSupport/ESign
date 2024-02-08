<?php

namespace NIIT\ESign\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\ESignFacade;

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
            ! ($notShowRoute && $document->statusIsNot(DocumentStatus::IN_PROGRESS)),
            404
        );

        $isSigned = ($signer->signingStatusIs(SigningStatus::SIGNED));

        if (! $notShowRoute && ! $isSigned) {
            return redirect()->route('esign.signing.index', [
                'signing_url' => $signer->url,
            ]);
        }

        Request::macro('signingHeaders', fn () => ESignFacade::signingHeaders());

        if ($request->expectsJson()) {
            Collection::macro('checkHeaders', function ($request) {
                $hasAllHeaders = true;

                $checkHeaders = function ($header, $value) use ($request, &$hasAllHeaders) {
                    $hasAllHeaders = $hasAllHeaders && $request->headers->contains($header, $value);
                };

                $this->each(function ($value, $header) use ($checkHeaders) {
                    $checkHeaders($header, $value);
                });

                return $hasAllHeaders;
            });

            abort_if(
                ! collect($request->signingHeaders())->checkHeaders($request),
                403,
                'Forbidden'
            );
        }

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
