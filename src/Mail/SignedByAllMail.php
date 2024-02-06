<?php

namespace NIIT\ESign\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use NIIT\ESign\Models\Document;
use Symfony\Component\Mime\Email;

class SignedByAllMail extends Mailable
{
    public function __construct(public Document $document)
    {
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
            from: new Address('test@example.com', 'test'),
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
        return [
            $this->document,
        ];
    }
}
