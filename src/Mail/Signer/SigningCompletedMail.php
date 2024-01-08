<?php

namespace NIIT\ESign\Mail\Signer;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use NIIT\ESign\Mail\Mailable;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;
use Symfony\Component\Mime\Email;

class SigningCompletedMail extends Mailable
{
    public function __construct(public Document $document, public Signer $signer)
    {
    }

    public function content(): Content
    {
        return new Content(
            view: 'esign::mails.signing-completed'
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Shipped',
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
