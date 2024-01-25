<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use NIIT\ESign\Enum\ReadStatus;
use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\Events\ReadStatusChanged;
use NIIT\ESign\Events\SigningProcessStarted;
use NIIT\ESign\Events\SigningStatusChanged;
use NIIT\ESign\Http\Requests\SigningRequest;
use NIIT\ESign\Http\Resources\SignerResource;
use NIIT\ESign\Models\DocumentSigner;

class SigningController extends Controller
{
    public function index(Request $request, DocumentSigner $signer)
    {
        SigningProcessStarted::dispatch($signer);

        $document = $signer->loadMissing('document.document', 'elements')->document;
        $formattedData = [json_decode((new SignerResource($signer))->toJson(), true)];

        return view('esign::documents.show', compact(
            'signer',
            'document',
            'formattedData',
        ));
    }

    public function store(SigningRequest $request, DocumentSigner $signer)
    {
        SigningStatusChanged::dispatch($signer, SigningStatus::SIGNED);

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function mailTrackingPixel(DocumentSigner $signer)
    {
        $pixel = sprintf('%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c', 71, 73, 70, 56, 57, 97, 1, 0, 1, 0, 128, 255, 0, 192, 192, 192, 0, 0, 0, 33, 249, 4, 1, 0, 0, 0, 0, 44, 0, 0, 0, 0, 1, 0, 1, 0, 0, 2, 2, 68, 1, 0, 59);

        $response = response($pixel, 200)
            ->header('Content-type', 'image/gif')
            ->header('Content-Length', 42)
            ->header('Cache-Control', 'private, no-cache, no-cache=Set-Cookie, proxy-revalidate')
            ->header('Expires', 'Wed, 11 Jan 2000 12:59:00 GMT')
            ->header('Last-Modified', 'Wed, 11 Jan 2006 12:59:00 GMT')
            ->header('Pragma', 'no-cache');

        ReadStatusChanged::dispatch($signer, ReadStatus::OPENED);

        return $response;
    }
}
