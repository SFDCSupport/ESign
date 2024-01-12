<?php

namespace NIIT\ESign\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NIIT\ESign\Enum\ReadStatus;
use NIIT\ESign\Models\DocumentSigner;

class ReadStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DocumentSigner $signer,
        public ReadStatus $status
    ) {
    }
}
