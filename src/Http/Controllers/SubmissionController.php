<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use NIIT\ESign\Models\Document;

class SubmissionController extends Controller
{
    public function index(Request $request, Document $document)
    {
        return view('esign::submissions.index', compact('document'));
    }

    public function show(Request $request, Document $document, DocumentSubmission $submission)
    {
        return view('esign::submissions.show', compact('document', 'submission'));
    }

    public function destroy(Request $request, Document $document, DocumentSubmission $submission)
    {
        $submission->delete();

        return back();
    }
}
