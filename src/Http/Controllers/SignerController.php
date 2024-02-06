<?php

namespace NIIT\ESign\Http\Controllers;

use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Events\DocumentStatusChanged;
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
        if ($document->status === DocumentStatus::IN_PROGRESS) {
            return $this->jsonResponse([
                'status' => 1,
                'redirect' => route('esign.documents.submissions.index', $document),
            ])->notify(
                __('esign::validations.document_is_in_progress'),
                'error',
            );
        }

        $response = $documentData = [];
        $validatedData = $request->validated();
        $mode = $validatedData['mode'];
        $title = $validatedData['title'] ?? null;
        $status = $validatedData['status'] ?? config('esign.defaults.document_status');
        $notificationSequence = $validatedData['notification_sequence'] ?? config('esign.defaults.document_status');
        $isSync = false;

        if ($document->status !== $status) {
            $documentData['status'] = $status;
        }

        if ($document->notification_sequence !== $notificationSequence) {
            if ($notificationSequence === NotificationSequence::SYNC) {
                $isSync = true;
            }

            $documentData['notification_sequence'] = $notificationSequence;
        }

        if (! blank($title) && $document->title !== $title) {
            $documentData['title'] = $title;
        }

        foreach ($validatedData['signers'] as $i => $signer) {
            $documentId = $request->document_id;
            $isSignerDeleted = $signer['is_deleted'] ?? false;

            $update = [
                'text' => $signer['text'] ?? __('esign::label.nth_signer', ['nth' => ordinal($i)]),
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
                    'text' => $element['text'] ?? str($element['eleType'])->title()->value(),
                    'type' => $element['eleType'],
                    'page_index' => $element['page_index'],
                    'page_width' => $element['page_width'],
                    'page_height' => $element['page_height'],
                    'width' => $element['width'],
                    'height' => $element['height'],
                    'left' => $element['left'],
                    'top' => $element['top'],
                    'position' => $element['position'] ?? ($index + 1),
                    'is_required' => $element['is_required'] ?? true,
                    'deleted_by' => $isElementDeleted ? $request->user()->id : null,
                    'is_next_receiver' => ! $isSync || $index === 0,
                ]);

                if ($isElementDeleted) {
                    $elementModel->delete();
                }

                $response[$signer['uuid']]['elements'][$element['uuid']] = $elementModel->id;
            }
        }

        if (! blank($documentData)) {
            $document->update($documentData);

            if ($document->status === DocumentStatus::IN_PROGRESS) {
                DocumentStatusChanged::dispatch($document, DocumentStatus::IN_PROGRESS);
                dd('hello');
            }
        }

        $return['status'] = 1;
        $return['data'] = $response;

        if ($mode === 'send') {
            $return['redirect'] = route('esign.documents.submissions.index', $document);
        }

        return $this->jsonResponse(
            $return
        )->notify('Success');
    }

    public function destroy(SignerRequest $request, Document $document, Signer $signer)
    {
    }
}
