<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Events\DocumentSignedBySigner;

class SignedBySigner
{
    public function handle(DocumentSignedBySigner $event): void
    {
        ($document = $event->document)->logAuditTrait(
            document: $document,
            event: 'document signed',
            signer: $event->signer,
            metadata: $event->metadata
        );
    }
}
