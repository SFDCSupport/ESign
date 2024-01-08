<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use NIIT\ESign\Http\Requests\DocumentRequest;
use NIIT\ESign\Models\Document;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        return view('esign::index');
    }

    public function create(Request $request)
    {
    }

    public function store(DocumentRequest $request)
    {
    }

    public function show(Document $document)
    {
    }

    public function edit(Document $document)
    {
    }

    public function update(DocumentRequest $request, Document $document)
    {
    }

    public function destroy(DocumentRequest $request, Document $document)
    {
    }

    public function bulkDestroy(DocumentRequest $request, Document $document)
    {
    }
}
