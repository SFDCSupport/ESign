<?php

namespace NIIT\ESign\Http\Controllers;

use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Http\Requests\SignerRequest;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;
use NIIT\ESign\Models\SignerElement;

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
        $documentData = [];
        $validatedData = $request->validated();
        $title = $validatedData['title'] ?? null;
        $notificationSequence = $validatedData['notification_sequence'] ?? NotificationSequence::ASYNC;

        if ($document->notification_sequence !== $notificationSequence) {
            $documentData['notification_sequence'] = $notificationSequence;
        }

        if (! blank($title) && $document->title !== $title) {
            $documentData['title'] = $title;
        }

        if (! blank($documentData)) {
            $document->update($documentData);
        }

        foreach ($validatedData['signers'] as $i => $signer) {
            $documentId = $request->document_id;
            $isSignerDeleted = $signer['is_deleted'] ?? false;

            $update = [
                'label' => $signer['label'] ?? __('esign::label.nth_signer', ['nth' => ordinal($i)]),
                'position' => $signer['position'] ?? ($i + 1),
                'deleted_by' => $isSignerDeleted ? $request->user()->id : null,
            ];

            if (isset($signer['email'])) {
                $update['email'] = $signer['email'];
            }

            /** @var Signer $signerModel */
            $signerModel = $document->signers()->updateOrCreate([
                'id' => $signer['id'] ?? null,
                'document_id' => $documentId,
            ], $update);

            if ($isSignerDeleted) {
                $signerModel->delete();
            }

            $response[$signer['uuid']]['id'] = $signerModel->id;

            foreach (($signer['elements'] ?? []) as $index => $element) {
                $isElementDeleted = $element['is_deleted'] ?? false;

                /** @var SignerElement $elementModel */
                $elementModel = $signerModel->elements()->updateOrCreate([
                    'id' => $element['id'] ?? null,
                    'signer_id' => $signerModel->id,
                    'document_id' => $documentId,
                ], [
                    'label' => $element['label'] ?? str($element['eleType'])->title()->value(),
                    'type' => $element['eleType'],
                    'on_page' => $element['on_page'],
                    'width' => $element['width'],
                    'height' => $element['height'],
                    'left' => $element['left'],
                    'top' => $element['top'],
                    'scale_x' => $element['scale_x'] ?? null,
                    'scale_y' => $element['scale_y'] ?? null,
                    'position' => $element['position'] ?? ($index + 1),
                    'deleted_by' => $isElementDeleted ? $request->user()->id : null,
                ]);

                if ($isElementDeleted) {
                    $elementModel->delete();
                }

                $response[$signer['uuid']]['elements'][$element['uuid']] = $elementModel->id;
            }
        }

        return $this->jsonResponse([
            'status' => 1,
            'data' => $response,
        ])->notify('Success');
    }

    public function destroy(SignerRequest $request, Document $document, Signer $signer)
    {
    }
}
