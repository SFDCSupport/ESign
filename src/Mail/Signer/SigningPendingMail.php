<?php

namespace NIIT\ESign\Mail\Signer;

use Illuminate\Mail\Mailables\Content;
use NIIT\ESign\Mail\Mailable;
use NIIT\ESign\Models\Signer;

class SigningPendingMail extends Mailable
{
    public function __construct(public Signer $signer)
    {
    }

    public function content(): Content
    {
        return new Content(
            view: 'esign::mails.signing-pending'
        );
    }
}
