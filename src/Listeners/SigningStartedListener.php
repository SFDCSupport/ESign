<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\Events\SigningProcessStarted;

class SigningStartedListener
{
    public function handle(SigningProcessStarted $event): void
    {
        $event->signer->update([
            'signing_status' => SigningStatus::NOT_SIGNED,
        ]);
    }
}
