<?php

namespace NIIT\ESign\Observers;

use NIIT\ESign\Models\Attachment;

class AttachmentObserver extends Observer
{
    public function creating(Attachment $attachment): void
    {
        $attachment->version ??= 1;
    }
}
