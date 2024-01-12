<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Events\SigningStatusChanged;

class SigningStatusListener
{
    public function handle(SigningStatusChanged $event): void
    {
    }
}
