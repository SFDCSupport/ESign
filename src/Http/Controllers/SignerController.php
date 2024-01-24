<?php

namespace NIIT\ESign\Http\Controllers;

use NIIT\ESign\Http\Requests\SignerRequest;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\DocumentSigner;
use NIIT\ESign\Models\DocumentSignerElement;

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
        $response = [];

        foreach ($request->validated()['signers'] as $i => $signer) {
            $documentId = $request->document_id;

            /** @var DocumentSigner $signerModel */
            $signerModel = $document->signers()->updateOrCreate([
                'id' => $signer['id'] ?? null,
                'document_id' => $documentId,
            ], [
                'label' => $signer['label'],
                'position' => $signer['position'] ?? ($i + 1),
                'deleted_at' => $signer['is_deleted'] ? now() : null,
                'deleted_by' => $signer['is_deleted'] ? $request->user()->id : null,
            ]);

            $response[$signer['uuid']]['id'] = $signerModel->id;

            foreach ($signer['elements'] ?? [] as $index => $element) {
                /** @var DocumentSignerElement $elementModel */
                $elementModel = $signerModel->elements()->updateOrCreate([
                    'id' => $element['id'] ?? null,
                    'signer_id' => $signerModel->id,
                    'document_id' => $documentId,
                ], [
                    //                    'label' => $element['label'],
                    'type' => $element['type'],
                    'on_page' => $element['on_page'],
                    'width' => $element['width'],
                    'height' => $element['height'],
                    'offset_x' => $element['offset_x'],
                    'offset_y' => $element['offset_y'],
                    'position' => $element['position'] ?? ($index + 1),
                    'deleted_at' => $element['is_deleted'] ? now() : null,
                    'deleted_by' => $signer['is_deleted'] ? $request->user()->id : null,
                ]);

                $response[$signer['uuid']]['elements'][$element['uuid']] = $elementModel->id;
            }
        }

        return $this->jsonResponse([
            'status' => 1,
            'data' => $response,
        ])->notify('Success');
    }

    public function destroy(SignerRequest $request, Document $document, DocumentSigner $signer)
    {
    }

    public function bulkDestroy(SignerRequest $request, Document $document)
    {
    }
}
