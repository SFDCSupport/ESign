<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\Events\SendStatusChanged;
use NIIT\ESign\Models\Signer;

class SendStatusListener
{
    public function handle(SendStatusChanged $event): void
    {
        /** @var Signer $signer */
        $signer = $event->signer;

        /** @var SendStatus $status */
        $status = $event->status;

        $signer->update([
            'send_status' => $status,
        ]);
    }
}
