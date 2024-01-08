<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Events\DocumentSigned;

class SigningCompletedListener
{
    public function handle(DocumentSigned $event): void
    {
    }
}
