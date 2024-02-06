<?php

namespace NIIT\ESign\Listeners;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Events\DocumentStatusChanged;
use NIIT\ESign\Mail\Signer\SendSigningLink;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class DocumentStatusListener
{
    public Document $document;

    public DocumentStatus $status;

    public function handle(DocumentStatusChanged $event): void
    {
        /** @var Document document */
        $this->document = $event->document;

        /** @var DocumentStatus status */
        $this->status = $event->status;
        dd($this->getSignersMapping());
        if ($this->status === DocumentStatus::IN_PROGRESS) {
            $this->getSignersMapping()
                ->each(fn ($url, $email) => dd($url, $email));

            Mail::to()->send(
                new SendSigningLink($this->document)
            );
        }
    }

    protected function getSignersMapping(): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<Signer> $signers */
        $signers = $this->document->loadMissing([
            'signers' => fn ($q) => $q->orderBy('position'),
        ])->signers;

        if ($this->document->notification_sequence === NotificationSequence::ASYNC) {
            return $signers->pluck('url', 'email');
        }

        return $signers->where('send_status', 'not_sent')
            ->where('is_next_receiver', true)
            ->take(1)
            ->pluck('url', 'email');
    }
}
