<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Events\DocumentOpenedBySigner;

class OpenedBySigner
{
    public function handle(DocumentOpenedBySigner $event): void
    {
        ($document = $event->document)->logAuditTrait(
            document: $document,
            event: 'document opened',
            signer: $event->signer,
            metadata: $event->metadata
        );
    }
}
