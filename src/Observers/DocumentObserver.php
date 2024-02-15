<?php

namespace NIIT\ESign\Observers;

use NIIT\ESign\Enum\AuditEvent;
use NIIT\ESign\Models\Document;

class DocumentObserver extends Observer
{
    public function created(Document $document): void
    {
        $this->logAuditTrait(
            document: $document,
            event: AuditEvent::DOCUMENT_CREATED,
        );
    }

    public function updated(Document $document): void
    {
        $event = AuditEvent::DOCUMENT_UPDATED;
        $dirty = array_diff_key(
            $document->getdirty(),
            array_flip([
                'updated_at',
                'updated_by',
            ])
        );

        if (array_key_exists('status', $dirty)) {
            $event = AuditEvent::DOCUMENT_STATUS_CHANGED;
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
            event: AuditEvent::DOCUMENT_DELETED
        );
    }

    public function restored(Document $document): void
    {
        $this->logAuditTrait(
            document: $document,
            event: AuditEvent::DOCUMENT_RESTORED
        );
    }

    public function forceDeleted(Document $document): void
    {
        $this->logAuditTrait(
            document: $document,
            event: AuditEvent::DOCUMENT_DELETED_FORCE
        );
    }
}
