<?php

namespace NIIT\ESign\Mail;

use Illuminate\Mail\Mailables\Content;
use NIIT\ESign\Models\Document;

class SigningCompletedMail extends Mailable
{
    public function __construct(public Document $document)
    {
    }

    public function content(): Content
    {
        return new Content(
            view: 'esign::mails.signing-completed'
        );
    }
}
