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
            blank($interval = ESignFacade::config('heartbeat_interval')) ||
            ! str($response->headers->get('content-type'))->startsWith('text/html')
        ) {
            return $response;
        }

        $heartbeatRoute = route('esign.heartbeat');

        $javascriptCode = <<<JS
                <script>
                    setInterval(function () {
                        $.post(
                            '$heartbeatRoute',
                            {}
                            ).done((r) => {
                                console.log(r);
                            }).fail((x) => toast('error', x.responseText));
                    }, $interval);
                </script>
            JS;

        $content = $response->getContent();
        $content = str_replace('</body>', $javascriptCode.'</body>', $content);

        $response->setContent($content);

        return $response;
    }
}
