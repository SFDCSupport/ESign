<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Events\DocumentStatusChanged;

class DocumentStatusListener
{
    public function handle(DocumentStatusChanged $event): void
    {
    }
}
