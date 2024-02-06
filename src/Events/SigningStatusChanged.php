<?php

namespace NIIT\ESign\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class SigningStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Document $document,
        public Signer $signer,
        public SigningStatus $status
    ) {
    }
}
