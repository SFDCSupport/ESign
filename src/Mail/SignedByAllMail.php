<?php

namespace NIIT\ESign\Mail;

use App\Actions\FilepondAction;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use NIIT\ESign\Models\Document;
use Symfony\Component\Mime\Email;

class SignedByAllMail extends Mailable
{
    public function __construct(
        public Document $document,
        public ?string $signedDocumentPath = null
    ) {
    }

    public function content(): Content
    {
        return new Content(
            view: 'esign::mails.signed-by-all'
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('esign::label.signed_by_all'),
            using: [
                function (Email $message) {
                    // ...
                },
            ]
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        /** @var \NIIT\ESign\Models\Attachment $attachment */
        $attachment = $this->document->loadMissing('document')->document;

        return [
            Attachment::fromStorageDisk(
                $attachment->disk ?? FilepondAction::getDisk(true),
                $this->signedDocumentPath ?? $this->document->getSignedDocumentPath()
            )->as(
                $this->document->title.'.'.$attachment->extension
            )->withMime(
                'application/pdf'
            ),
        ];
    }
}
