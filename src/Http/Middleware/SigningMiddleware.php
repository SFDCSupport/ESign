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
        $document = $loadedModel->document;

        /**
         * abort_if: signer not exists
         * abort_if: document status is not draft | completed
         */
        abort_if(
            ! $signer ||
            ! $document->statusIs(
                DocumentStatus::COMPLETED,
                DocumentStatus::IN_PROGRESS,
            ),
            404);

        $isIndexRoute = $request->routeIs('esign.signing.index');

        /**
         * abort_if: route is signing index with document without in progress state
         */
        abort_if(
            $document->statusIsNot(DocumentStatus::IN_PROGRESS) &&
            $isIndexRoute,
            404
        );

        $isSigned = $signer->signingStatusIs(SigningStatus::SIGNED);

        /**
         * abort_if: route is signing index with signed signer
         */
        abort_if(
            $request->routeIs('esign.signing.show') && ! $isSigned,
            404
        );

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
            $isSigned && $isIndexRoute
        ) {
            return redirect()->route('esign.signing.show', [
                'signing_url' => $signer->url,
            ]);
        }

        return $next($request);
    }
}
