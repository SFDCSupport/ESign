<?php

namespace NIIT\ESign\Events\Signer;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SigningMailSending
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct()
    {
    }
}
