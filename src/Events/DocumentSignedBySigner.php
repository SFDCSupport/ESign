<?php

namespace NIIT\ESign\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\DocumentSigner;

class DocumentSignedBySigner
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Document $document,
        public DocumentSigner $signer,
        public array $metadata
    ) {
    }
}
