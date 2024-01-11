<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Enum\SignerStatus;
use NIIT\ESign\Events\DocumentSignedBySigner;
use NIIT\ESign\Models\DocumentSigner;

class SignedBySigner
{
    public function handle(DocumentSignedBySigner $event): void
    {
        /** @var DocumentSigner $signer */
        $signer = $event->signer;

        $signer->update([
            'status' => SignerStatus::DOC_SIGNED,
        ]);

        ($document = $signer->document)->logAuditTrait(
            document: $document,
            event: 'document signed',
            signer: $signer,
            metadata: $event->metadata
        );
    }
}
