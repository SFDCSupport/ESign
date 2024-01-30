<?php

namespace NIIT\ESign\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NIIT\ESign\Models\Signer;

class SigningProcessStarted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Signer $signer
    ) {
    }
}
