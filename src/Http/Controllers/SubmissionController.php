<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;
use NIIT\ESign\Models\Submission;

class SubmissionController extends Controller
{
    public function index(Request $request, Document $document)
    {
        $document->loadMissing(['signers' => fn ($q) => $q->orderBy('position'), 'signers.submissions']);

        return $this->view('esign::submissions.index', compact('document'));
    }

    public function show(Request $request, Document $document, Signer $signer)
    {
        return $this->view('esign::submissions.show', compact('document', 'signer'));
    }

    public function destroy(Request $request, Document $document, Submission $submission)
    {
        $submission->delete();

        return back();
    }
}
