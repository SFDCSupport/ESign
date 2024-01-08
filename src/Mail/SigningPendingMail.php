<?php

namespace NIIT\ESign\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class SigningPendingMail extends Mailable
{
    public function __construct(public Signer $signer, public Document $document)
    {
    }

    public function content(): Content
    {
        return new Content(
            view: 'esign::mails.signing-pending'
        );
    }
}
