<?php

namespace NIIT\ESign\Http\Controllers;

use NIIT\ESign\Http\Requests\SignerRequest;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\DocumentSigner;

class SignerController extends Controller
{
    public function index(Document $document)
    {
        $document->loadMissing('signers');

        return response()->json([
            'signers' => $document->signers->pluck('email', 'id'),
        ]);
    }

    public function store(SignerRequest $request, Document $document)
    {
    }

    public function destroy(SignerRequest $request, Document $document, DocumentSigner $signer)
    {
    }

    public function bulkDestroy(SignerRequest $request, Document $document)
    {
    }
}
