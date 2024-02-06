<?php

namespace NIIT\ESign\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\SigningStatus;

class SigningMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $signer = $request->signer();
        $loadedModel = $request->signer()->loadMissing('document');

        abort_if(! $signer || $loadedModel->document->status !== DocumentStatus::IN_PROGRESS, 404);

        abort_if(
            $request->expectsJson() && ! $request->headers->has('X-ESign'),
            403,
            'Forbidden'
        );

        config(['filesystems.disks.esign_temp' => [
            'driver' => 'local',
            'root' => storage_path('app/esign_temp/'.$signer->id),
            'throw' => false,
        ]]);

        if (
            $signer->signing_status === SigningStatus::SIGNED &&
            ! $request->routeIs('esign.signing.show')
        ) {
            $disk = Storage::disk('esign_temp');

            abort_if(! $disk->fileExists($file = $signer->document->id.'.pdf'), 500);

            return redirect()->route('esign.signing.show', ['signing_url' => $signer->url]);
            //            return $disk->download(
            //                $file
            //            );
        }

        return $next($request);
    }
}
