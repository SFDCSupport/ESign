<?php
/*
 * @author : Anand Pilania
 * @mailto : Anand.Pilania@niit.com
 * @updated : 1/25/24, 11:35 AM
 */

namespace NIIT\ESign\Observers;

use Illuminate\Support\Str;
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
            document: $signer->document,
            event: 'signer-added',
            signer: $signer
        );
    }

    public function updated(Signer $signer): void
    {
        $event = 'signer-updated';
        $dirty = $signer->getDirty();

        if (array_key_exists('signing_status', $dirty)) {
            $event = 'signer-signing-status-changed';
        } elseif (array_key_exists('read_status', $dirty)) {
            $event = 'signer-read-status-changed';
        } elseif (array_key_exists('send_status', $dirty)) {
            $event = 'signer-send-status-changed';
        }

        $this->logAuditTrait(
            document: $signer->document,
            event: $event,
            signer: $signer,
            metadata: $dirty
        );
    }

    public function deleted(Signer $signer): void
    {
        $this->logAuditTrait(
            document: $signer->document,
            event: 'signer-deleted',
            signer: $signer
        );
    }

    public function restored(Signer $signer): void
    {
        $this->logAuditTrait(
            document: $signer->document,
            event: 'signer-restored',
            signer: $signer
        );
    }

    public function forceDeleted(Signer $signer): void
    {
        $this->logAuditTrait(
            document: $signer->document,
            event: 'signer-force-deleted',
            signer: $signer
        );
    }
}
