<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Enum\ReadStatus;
use NIIT\ESign\Events\ReadStatusChanged;
use NIIT\ESign\Models\Signer;

class ReadStatusListener
{
    public function handle(ReadStatusChanged $event): void
    {
        /** @var Signer $signer */
        $signer = $event->signer;

        /** @var ReadStatus $status */
        $status = $event->status;
        ds('listening read status', $status);

        $signer->update([
            'read_status' => $status,
        ]);
    }
}
