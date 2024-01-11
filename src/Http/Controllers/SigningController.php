<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use NIIT\ESign\Events\DocumentOpenedBySigner;
use NIIT\ESign\Events\DocumentSignedBySigner;
use NIIT\ESign\Events\MailReceivedBySigner;
use NIIT\ESign\Http\Requests\SigningRequest;
use NIIT\ESign\Models\DocumentSigner;

class SigningController extends Controller
{
    public function index(Request $request, DocumentSigner $signer)
    {
        DocumentOpenedBySigner::dispatch($signer);

        return view('esign::index');
    }

    public function store(SigningRequest $request, DocumentSigner $signer)
    {
        DocumentSignedBySigner::dispatch($signer);

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

        MailReceivedBySigner::dispatch($signer);

        return $response;
    }
}
