<?php

namespace NIIT\ESign\Observers;

use NIIT\ESign\Models\DocumentSignerElement as Element;

class DocumentSignerElementObserver
{
    public function creating(Element $element): void
    {
        if (
            ! blank($element->position) ||
            (
                blank($signerId = $element->signer_id) ||
                blank($documentId = $element->document_id)
            )
        ) {
            return;
        }

        $maxPriority = Element::where([
            'signer_id' => $signerId,
            'document_id' => $documentId,
        ])->max('position') ?? 0;

        $element->position = $maxPriority + 1;
    }

    public function created(Element $element): void
    {
        //
    }

    public function updated(Element $element): void
    {
        //
    }

    public function deleted(Element $element): void
    {
        //
    }

    public function restored(Element $element): void
    {
        //
    }

    public function forceDeleted(Element $element): void
    {
        //
    }
}
