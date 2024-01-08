<?php

namespace NIIT\ESign\Mail;

use Illuminate\Mail\Mailables\Content;
use NIIT\ESign\Models\ESignDocument;
use NIIT\ESign\Models\ESignDocumentSigner;

class SigningLinkMail extends Mailable
{
    public function __construct(public ESignDocumentSigner $signer, public ESignDocument $document)
    {
    }

    public function content(): Content
    {
        return new Content(
            view: 'esign::mails.signing-link'
        );
    }
}
