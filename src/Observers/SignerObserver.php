<?php
/*
 * @author : Anand Pilania
 * @mailto : Anand.Pilania@niit.com
 * @updated : 1/25/24, 11:35 AM
 */

namespace NIIT\ESign\Observers;

use Illuminate\Support\Str;
use NIIT\ESign\Models\Signer;

class SignerObserver
{
    public function creating(Signer $signer): void
    {
        $signer->url = Str::orderedUuid();

        if (
            ! blank($signer->position) ||
            blank($documentId = $signer->document_id)
        ) {
            return;
        }

        $maxPriority = Signer::where('document_id', $documentId)
            ->max('position') ?? 0;

        $signer->position = $maxPriority + 1;
    }

    public function created(Signer $signer): void
    {
        //
    }

    public function updated(Signer $signer): void
    {
        //
    }

    public function deleted(Signer $signer): void
    {
        //
    }

    public function restored(Signer $signer): void
    {
        //
    }

    public function forceDeleted(Signer $signer): void
    {
        //
    }
}
