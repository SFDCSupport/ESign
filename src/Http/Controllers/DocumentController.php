<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use NIIT\ESign\Http\Requests\DocumentRequest;
use NIIT\ESign\Models\Document;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::where('created_by', $this->user($request)->id)
            ->all();

        return view('esign::documents.index', compact('documents'));
    }

    public function create(Request $request)
    {
        return view('esign::documents.create');
    }

    public function store(DocumentRequest $request)
    {
        $document = Document::create($request->all());

        return redirect()->route('esign.documents.show', $document);
    }

    public function show(Document $document)
    {
        return view('esign::documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        return view('esign::documents.create', compact('document'));
    }

    public function update(DocumentRequest $request, Document $document)
    {
        $document->update($request->all());

        return redirect()->route('esign.documents.index');
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return back();
    }

    public function bulkDestroy(DocumentRequest $request)
    {
        foreach (Document::find($request->get('ids')) as $document) {
            $document->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function copy(Document $document)
    {
        $replica = $document->replicate();
        $replica->save();

        return redirect()->route('esign.documents.show', $replica);
    }

    public function send(Document $document) {

        return redirect()->route('esign.documents.show', $document);
    }
}
