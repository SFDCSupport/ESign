<?php

namespace NIIT\ESign\Mail\Signer;

use Illuminate\Mail\Mailables\Content;
use NIIT\ESign\Mail\Mailable;
use NIIT\ESign\Models\DocumentSigner;

class SigningPendingMail extends Mailable
{
    public function __construct(public DocumentSigner $signer)
    {
    }

    public function content(): Content
    {
        return new Content(
            view: 'esign::mails.signing-pending'
        );
    }
}
