<?php

namespace NIIT\ESign\Observers;

use NIIT\ESign\Models\Document;

class DocumentObserver extends Observer
{
    public function created(Document $document): void
    {
        $this->logAuditTrait($document, 'document-created');
    }

    public function updated(Document $document): void
    {
        $event = 'document-updated';
        $dirty = $document->getdirty();

        if (array_key_exists('status', $dirty)) {
            $event = 'document-status-changed';
        }

        $this->logAuditTrait($document, $event, null, null, $dirty);
    }

    public function deleted(Document $document): void
    {
        $this->logAuditTrait($document, 'document-deleted');
    }

    public function restored(Document $document): void
    {
        $this->logAuditTrait($document, 'document-restored');
    }

    public function forceDeleted(Document $document): void
    {
        $this->logAuditTrait($document, 'document-force-deleted');
    }
}
