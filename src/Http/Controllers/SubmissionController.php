<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Submission;

class SubmissionController extends Controller
{
    public function index(Request $request, Document $document)
    {
        $document->load(['signers' => fn ($q) => $q->orderBy('position'), 'signers.submissions']);

        return view('esign::submissions.index', compact('document'));
    }

    public function show(Request $request, Document $document, Submission $submission)
    {
        return view('esign::submissions.show', compact('document', 'submission'));
    }

    public function destroy(Request $request, Document $document, Submission $submission)
    {
        $submission->delete();

        return back();
    }
}
