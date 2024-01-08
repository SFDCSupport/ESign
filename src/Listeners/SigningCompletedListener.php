<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Events\SigningProcessCompleted;

class SigningCompletedListener
{
    public function handle(SigningProcessCompleted $event): void
    {
    }
}
