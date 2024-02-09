<?php

namespace NIIT\ESign\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class DocumentStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Document $document,
        public DocumentStatus $status,
        public ?Signer $signer = null
    ) {
    }
}
