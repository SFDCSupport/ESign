<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Events\SigningProcessStarted;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class SigningStartedListener
{
    public function handle(SigningProcessStarted $event): void
    {
        /** @var Document $document */
        $document = $event->document;

        /** @var Signer $signer */
        $signer = $event->signer;

        $document->logAuditTrait(
            document: $document,
            event: 'signing-started',
            signer: $signer,
        );
    }
}
