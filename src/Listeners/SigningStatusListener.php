<?php

namespace NIIT\ESign\Listeners;

use Illuminate\Database\Eloquent\Collection;
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

        if ($signer->signingStatusIsNot($status)) {
            $signer->update([
                'is_next_receiver' => false,
                'signing_status' => $status,
            ]);
        }

        /** @var Collection<Signer> $signers */
        $signers = $document->loadMissing([
            'signers' => fn ($q) => $q->where('send_status', SendStatus::NOT_SENT)
                ->orderBy('position'),
        ])->signers;

        if ($document->notificationSequenceIs(NotificationSequence::SYNC)) {
            if ($nextSigner = $signers->where('position', '>', $signer->position)->first()) {
                $nextSigner->update([
                    'is_next_receiver' => true,
                ]);

                ESignFacade::sendSigningLink($nextSigner, $document);
            } else {
                $document->markAs(
                    status: DocumentStatus::COMPLETED,
                    signer: $signer
                );
            }
        } else {
            if (count($signers) > 0) {
                ds($signers)->label('signers');
                foreach ($signers as $signer) {
                    ESignFacade::sendSigningLink($signer, $document);
                }
            } else {
                $document->markAs(
                    status: DocumentStatus::COMPLETED,
                    signer: $signer
                );
            }
        }
    }
}
