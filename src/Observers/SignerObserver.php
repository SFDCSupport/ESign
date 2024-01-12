<?php

namespace NIIT\ESign\Observers;

use NIIT\ESign\Models\DocumentSigner as Signer;

class SignerObserver
{
    /**
     * Handle the Signer "created" event.
     */
    public function created(Signer $signer): void
    {
        //
    }

    /**
     * Handle the Signer "updated" event.
     */
    public function updated(Signer $signer): void
    {
        //
    }

    /**
     * Handle the Signer "deleted" event.
     */
    public function deleted(Signer $signer): void
    {
        //
    }

    /**
     * Handle the Signer "restored" event.
     */
    public function restored(Signer $signer): void
    {
        //
    }

    /**
     * Handle the Signer "force deleted" event.
     */
    public function forceDeleted(Signer $signer): void
    {
        //
    }
}
