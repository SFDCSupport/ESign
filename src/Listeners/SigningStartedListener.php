<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Events\SigningProcessStarted;

class SigningStartedListener
{
    public function handle(SigningProcessStarted $event): void
    {
    }
}
