<?php

namespace NIIT\ESign\Observers;

use NIIT\ESign\Models\Document;

class DocumentObserver
{
    public function created(Document $document): void
    {
        //
    }

    public function updated(Document $document): void
    {
        //
    }

    public function deleted(Document $document): void
    {
        //
    }

    public function restored(Document $document): void
    {
        //
    }

    public function forceDeleted(Document $document): void
    {
        //
    }
}
