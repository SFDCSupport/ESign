<?php

namespace NIIT\ESign\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NIIT\ESign\Models\Document;

class SendDocumentLink
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Document $document
    ) {
    }
}
