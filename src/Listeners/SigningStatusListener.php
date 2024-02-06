<?php

namespace NIIT\ESign\Listeners;

use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\ESignFacade;
use NIIT\ESign\Events\SigningStatusChanged;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class SigningStatusListener
{
    public function handle(SigningStatusChanged $event): void
    {
        /** @var Document $document */
        $document = $event->document;

        /** @var Signer $signer */
        $signer = $event->signer;

        /** @var SigningStatus $status */
        $status = $event->status;

        if ($signer->signing_status !== $status) {
            $signer->update([
                'is_next_receiver' => false,
                'signing_status' => $status,
            ]);
        }

        if ($document->notification_sequence === NotificationSequence::SYNC) {
            if ($nextSigner = $document->loadMissing([
                'signers' => fn ($q) => $q->where('signing_status', SendStatus::NOT_SENT)
                    ->where('position', '>', $signer->position)
                    ->orderBy('position'),
            ])->signers->first()
            ) {
                $nextSigner->update([
                    'is_next_receiver' => true,
                ]);

                ESignFacade::sendSigningLink($nextSigner, $document);
            } else {
                $document->markAs(DocumentStatus::COMPLETED);
            }
        } elseif (count(($signers = $document->loadMissing([
            'signers' => fn ($q) => $q->where('signing_status', SendStatus::NOT_SENT)
                ->orderBy('position'),
        ])->signers)) !== 0) {
            foreach ($signers as $signer) {
                ESignFacade::sendSigningLink($signer, $document);
            }
        } else {
            $document->markAs(DocumentStatus::COMPLETED);
        }
    }
}
