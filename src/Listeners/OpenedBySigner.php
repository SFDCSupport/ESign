<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Enum\SignerStatus;
use NIIT\ESign\Events\DocumentOpenedBySigner;
use NIIT\ESign\Models\DocumentSigner;

class OpenedBySigner
{
    public function handle(DocumentOpenedBySigner $event): void
    {
        /** @var DocumentSigner $signer */
        $signer = $event->signer;

        $signer->update([
            'status' => SignerStatus::DOC_OPENED,
        ]);

        ($document = $signer->document)->logAuditTrait(
            document: $document,
            event: 'document opened',
            signer: $signer,
            metadata: $event->metadata
        );
    }
}
