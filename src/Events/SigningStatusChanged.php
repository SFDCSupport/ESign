<?php

namespace NIIT\ESign\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\Models\DocumentSigner;

class SigningStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DocumentSigner $signer,
        public SigningStatus $status
    ) {
    }
}
