<?php

namespace NIIT\ESign\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NIIT\ESign\Enum\SignerStatus;
use NIIT\ESign\Models\DocumentSigner;

class MailReceivedBySigner
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DocumentSigner $signer
    ) {
        if (is_null($signer->status)) {
            $signer->status = SignerStatus::MAIL_READ;
            $signer->save();

            ($document = $signer->document)->logAuditTrait(
                document: $document,
                event: 'mail received',
                signer: $signer
            );
        }
    }
}
