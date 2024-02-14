<?php

namespace NIIT\ESign\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use NIIT\ESign\ESignFacade;

class Heartbeat
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if (
            $request->expectsJson() ||
            ! $response->isSuccessful() ||
            blank($interval = ESignFacade::config('intervals.heartbeat')) ||
            ! str($response->headers->get('content-type'))->startsWith('text/html')
        ) {
            return $response;
        }

        $documentId = $request->document?->id;
        $signerId = $request->signer?->id;
        $timestamp = now();
        $heartbeatRoute = route('esign.heartbeat');

        if ($request->routeIs('esign.signing.*')) {
            $signer = $request->signer();

            $documentId = $signer->document_id;
            $signerId = $signer->id;
        }

        $javascriptCode = <<<JS
                <script>
                    setInterval(function () {
                        $.post("$heartbeatRoute", {
                            timestamp: '$timestamp',
                            documentId: '$documentId',
                            signerId: '$signerId',
                        })
                        .done((r) => {
                            if(r.status !== 1) {
                                return;
                            }

                            if(!blank(r.toast)) {
                                toast(r.toast.type ?? 'info', r.toast.msg ?? r.toast);
                            }

                            if(!blank(r.redirect)) {
                                $(location).attr("href", r.redirect);
                            }
                        })
                        .fail((x) => toast("error", x.responseText));
                    }, $interval);
                </script>;
            JS;

        $content = $response->getContent();
        $content = str_replace('</body>', $javascriptCode.'</body>', $content);

        $response->setContent($content);

        return $response;
    }
}
