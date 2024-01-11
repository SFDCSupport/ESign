<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use NIIT\ESign\Events\SendDocumentLink;
use NIIT\ESign\Http\Requests\DocumentRequest;
use NIIT\ESign\Models\Document;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::with('creator', 'document')
            ->where('created_by', $this->user($request)->id)
            ->get();

        return view('esign::documents.index', compact('documents'));
    }

    public function create(Request $request)
    {
        return view('esign::documents.create');
    }

    public function store(DocumentRequest $request)
    {
        $document = Document::create($request->all());

        return $request->expectsJson()
            ? response()->json([
                'id' => $document->id,
                'redirect' => route('esign.documents.show', $document),
            ])
            : redirect()->route('esign.documents.show', $document);
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

        return back() ?? redirect()->route('esign.documents.index');
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
        $replica->push();

        $document->relations = [];
        $document->load('document');

        if ($document->document()->exists()) {
            $replica->document()->save($document->document);
        }

        return redirect()->route('esign.documents.show', $replica);
    }

    public function send(Document $document)
    {
        SendDocumentLink::dispatch($document);

        return redirect()->route('esign.documents.show', $document);
    }
}
