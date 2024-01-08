<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Events\DocumentOpenedBySigner;

class OpenedBySigner
{
    public function handle(DocumentOpenedBySigner $event): void
    {
    }
}
