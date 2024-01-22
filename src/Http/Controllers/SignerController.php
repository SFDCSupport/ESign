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
        foreach ($request->validated() as $i => $signer) {
            /** @var DocumentSigner $signerModel */
            $signerModel = $document->signers()->updateOrCreate([
                'id' => $signer->id ?? null,
            ], [
                'label' => $signer->label,
                'position' => $signer->position ?? ($i + 1),
            ]);

            foreach ($signer['elements'] ?? [] as $index => $element) {
                /** @var DocumentSignerElement $elementModel */
                $elementModel = $signerModel->elements()->updateOrCreate([
                    'id' => $element->id ?? null,
                ], [
                    'document_id' => $signer->document_id,
                    'type' => $element->type,
                    'on_page' => $element->on_page,
                    'width' => $element->width,
                    'height' => $element->height,
                    'x_axis' => $element->offset_x,
                    'y_axis' => $element->offset_y,
                    'position' => $element->position ?? ($index + 1),
                ]);
            }
        }

        return $this->jsonResponse([
            'status' => 1,
        ]);
    }

    public function destroy(SignerRequest $request, Document $document, DocumentSigner $signer)
    {
    }

    public function bulkDestroy(SignerRequest $request, Document $document)
    {
    }
}
