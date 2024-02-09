<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\ESignFacade;
use NIIT\ESign\Events\DocumentStatusChanged;
use NIIT\ESign\Events\SigningProcessCompleted;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class DocumentStatusListener
{
    public function handle(DocumentStatusChanged $event): void
    {
        /** @var Document $document */
        $document = $event->document;

        /** @var DocumentStatus $status */
        $status = $event->status;

        /** @var ?Signer $signer */
        $signer = $event->signer;

        if ($status === DocumentStatus::IN_PROGRESS) {
            /** @var \Illuminate\Database\Eloquent\Collection<Signer> $signers */
            $signers = $document->loadMissing([
                'signers' => fn ($q) => $q->where('send_status', SendStatus::NOT_SENT)
                    ->orderBy('position'),
            ])->signers;

            if ($document->notificationSequenceIs(NotificationSequence::ASYNC)) {
                $signers->each(fn ($signer) => ESignFacade::sendSigningLink($signer, $document));
            } else {
                $_signer = $signers->where('send_status', SendStatus::NOT_SENT)
                    ->first();

                ESignFacade::sendSigningLink($_signer, $document);
            }
        }

        if ($status === DocumentStatus::COMPLETED && $signer) {
            SigningProcessCompleted::dispatch(
                $document, $signer
            );
        }
    }
}
