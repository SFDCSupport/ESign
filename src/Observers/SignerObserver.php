<?php
/*
 * @author : Anand Pilania
 * @mailto : Anand.Pilania@niit.com
 * @updated : 1/25/24, 11:35 AM
 */

namespace NIIT\ESign\Observers;

use Illuminate\Support\Str;
use NIIT\ESign\Enum\AuditEvent;
use NIIT\ESign\Models\Signer;

class SignerObserver extends Observer
{
    public function creating(Signer $signer): void
    {
        $signer->url = Str::orderedUuid();

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
        $this->logAuditTrait(
            document: $signer->loadMissing('document')->document,
            event: AuditEvent::SIGNER_ADDED,
            signer: $signer,
        );
    }

    public function updated(Signer $signer): void
    {
        $event = AuditEvent::SIGNER_UPDATED;
        $dirty = array_diff_key(
            $signer->getdirty(),
            array_flip([
                'updated_at',
                'updated_by',
            ])
        );

        if (array_key_exists('signing_status', $dirty)) {
            $event = AuditEvent::SIGNER_SIGNING_STATUS_CHANGED;
        } elseif (array_key_exists('read_status', $dirty)) {
            $event = AuditEvent::SIGNER_READ_STATUS_CHANGED;
        } elseif (array_key_exists('send_status', $dirty)) {
            $event = AuditEvent::SIGNER_SEND_STATUS_CHANGED;
        }

        $this->logAuditTrait(
            document: $signer->loadMissing('document')->document,
            event: $event,
            signer: $signer,
            metadata: $dirty,
        );
    }

    public function deleted(Signer $signer): void
    {
        $this->logAuditTrait(
            document: $signer->loadMissing('document')->document,
            event: AuditEvent::SIGNER_DELETED,
            signer: $signer,
        );
    }

    public function restored(Signer $signer): void
    {
        $this->logAuditTrait(
            document: $signer->loadMissing('document')->document,
            event: AuditEvent::SIGNER_RESTORED,
            signer: $signer,
        );
    }

    public function forceDeleted(Signer $signer): void
    {
        $this->logAuditTrait(
            document: $signer->loadMissing('document')->document,
            event: AuditEvent::SIGNER_DELETED_FORCE,
            signer: $signer,
        );
    }
}
