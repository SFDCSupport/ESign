<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Http\Requests\DocumentRequest;
use NIIT\ESign\Http\Resources\SignerResource;
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
        if ($document->status === DocumentStatus::PENDING) {
            return redirect()->route('esign.documents.submissions.index', $document);
        }

        $loadedRelations = $document->loadMissing('document', 'signers.elements');

        $formattedData = [
            'status' => $document->status,
            'notification_sequence' => $document->notification_sequence,
            'signers' => $this->mergeWhen(
                $loadedRelations->signers->count() > 0,
                SignerResource::collection($loadedRelations->signers),
                [['label' => '1st Signer', 'position' => 1, 'elements' => []]]
            )->data,
        ];

        return view('esign::documents.show', compact(
            'document',
            'formattedData',
        ));
    }

    public function edit(Document $document)
    {
        return redirect()->route('esign.documents.show', $document);
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
        $replica->parent_id = $document->id;
        $replica->title = $document->title.' ('.__('esign::label.copy').')';
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
        return redirect()->route('esign.documents.show', $document);
    }
}
