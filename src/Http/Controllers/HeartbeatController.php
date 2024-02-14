<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class HeartbeatController extends Controller
{
    public function __invoke(Request $request)
    {
        $toast = [];
        $redirect = null;
        $timestamp = $request->get('timestamp');

        if ($request->get('signerId')) {
            $signer = Signer::with('document.document')->find($request->get('signerId'));

            if ($signer->document->document->updated_at->gt($timestamp)) {
                $toast['type'] = 'warning';
                $toast['msg'] = 'Document updated! Redirecting...';
                $redirect = $signer->signingUrl();
            }
        } elseif ($request->get('documentId')) {
            $document = Document::with('document')->find($request->get('documentId'));

            if ($document->document->updated_at->gt($timestamp)) {
                $toast = 'Document updated! Redirecting...';
                $redirect = $request->route();
            }
        }

        return $this->jsonResponse([
            'status' => 1,
            'toast' => $toast,
            //'redirect' => $redirect,
        ]);
    }
}
