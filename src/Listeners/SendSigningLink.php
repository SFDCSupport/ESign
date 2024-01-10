<?php

namespace NIIT\ESign\Listeners;

use Illuminate\Support\Facades\Mail;
use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Enum\SignerStatus;
use NIIT\ESign\Events\SendDocumentLink;
use NIIT\ESign\Mail\Signer\SendSigningLink as MailLink;
use NIIT\ESign\Models\Document;

class SendSigningLink
{
    public function handle(SendDocumentLink $event): void
    {
        /** @var Document $document */
        $document = $event->document;

        if ($document->link_sent_to_all) {
            return;
        }

        $isAsyncSigners = $document->notification_sequence === NotificationSequence::ASYNC;
        $signers = $document->loadMissing('signers')->signers->pluck('email');

        if (! $isAsyncSigners) {
            $signers[] = $document->signers()->where(
                'mail_status', SignerStatus::MAIL_NOT_RECEIVED
            )->oldest('priority')->first()->email;
        }

        Mail::to($signers)
            ->send(
                new MailLink($document)
            );
    }
}
