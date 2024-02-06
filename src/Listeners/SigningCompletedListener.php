<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Events\SigningProcessCompleted;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class SigningCompletedListener
{
    public function handle(SigningProcessCompleted $event): void
    {
        /** @var Document $document */
        $document = $event->document;

        /** @var Signer $signer */
        $signer = $event->signer;

        $document->update([
            'status' => DocumentStatus::COMPLETED,
        ]);

        $document->logAuditTrait(
            document: $document,
            event: 'signing-completed',
            signer: $signer,
        );
    }
}
