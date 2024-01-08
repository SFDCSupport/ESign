<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Events\DocumentSignedBySigner;

class SignedBySigner
{
    public function handle(DocumentSignedBySigner $event): void
    {
    }
}
