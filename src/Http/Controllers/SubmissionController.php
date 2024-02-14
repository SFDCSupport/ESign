<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class SubmissionController extends Controller
{
    public function index(Request $request, Document $document)
    {
        $document->loadMissing(['signers' => fn ($q) => $q->orderBy('position'), 'signers.elements:id,signer_id,data,submitted_at']);

        return $this->view('esign::submissions.index', compact('document'));
    }

    public function show(Request $request, Document $document, Signer $signer)
    {
        $signer->loadMissing('elements');

        return $this->view('esign::submissions.show', compact('document', 'signer'));
    }

    public function destroy(Request $request, Document $document)
    {
        return back();
    }
}
