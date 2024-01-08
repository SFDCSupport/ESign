<?php

namespace NIIT\ESign\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use NIIT\ESign\Models\ESignDocument;
use NIIT\ESign\Models\ESignDocumentSigner;

class SigningPendingMail extends Mailable
{
    public function __construct(public ESignDocumentSigner $signer, public ESignDocument $document)
    {
    }

    public function content(): Content
    {
        return new Content(
            view: 'esign::mails.signing-pending'
        );
    }
}
