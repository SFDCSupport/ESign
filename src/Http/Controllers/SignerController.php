<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use NIIT\ESign\Http\Requests\SignerRequest;
use NIIT\ESign\Models\Document;

class SignerController extends Controller
{
    public function index(Request $request, Document $document)
    {
        //
    }

    public function store(SignerRequest $request, Document $document)
    {
    }

    public function destroy(SignerRequest $request, Document $document)
    {
    }

    public function bulkDestroy(SignerRequest $request, Document $document)
    {
    }
}
