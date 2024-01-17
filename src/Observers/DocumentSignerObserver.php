<?php

namespace NIIT\ESign\Observers;

use NIIT\ESign\Models\DocumentSigner as Signer;

class DocumentSignerObserver
{
    public function creating(Signer $signer): void
    {
        if (
            ! blank($signer->position) ||
            blank($documentId = $signer->document_id)
        ) {
            return;
        }

        $maxPriority = Signer::where('document_id', $documentId)
            ->max('position') ?? 0;

        $signer->position = $maxPriority + 1;
    }

    public function created(Signer $signer): void
    {
        //
    }

    public function updated(Signer $signer): void
    {
        //
    }

    public function deleted(Signer $signer): void
    {
        //
    }

    public function restored(Signer $signer): void
    {
        //
    }

    public function forceDeleted(Signer $signer): void
    {
        //
    }
}
