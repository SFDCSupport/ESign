<?php

namespace NIIT\ESign\Observers;

use NIIT\ESign\Models\Document;

class DocumentObserver extends Observer
{
    public function created(Document $document): void
    {
        $this->logAuditTrait(
            document: $document,
            event: 'document-created'
        );
    }

    public function updated(Document $document): void
    {
        $event = 'document-updated';
        $dirty = array_diff_key(
            $document->getdirty(),
            array_flip([
                'updated_at',
                'updated_by',
            ])
        );

        if (array_key_exists('status', $dirty)) {
            $event = 'document-status-changed';
        }

        $this->logAuditTrait(
            document: $document,
            event: $event,
            metadata: $dirty
        );
    }

    public function deleted(Document $document): void
    {
        $this->logAuditTrait(
            document: $document,
            event: 'document-deleted'
        );
    }

    public function restored(Document $document): void
    {
        $this->logAuditTrait(
            document: $document,
            event: 'document-restored'
        );
    }

    public function forceDeleted(Document $document): void
    {
        $this->logAuditTrait(
            document: $document,
            event: 'document-force-deleted'
        );
    }
}
