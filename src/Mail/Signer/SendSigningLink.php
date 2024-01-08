<?php
/*
 * @author : Anand Pilania
 * @mailto : Anand.Pilania@niit.com
 * @updated : 1/8/24, 8:45 PM
 */

namespace NIIT\ESign\Mail\Signer;

use Illuminate\Mail\Mailables\Content;
use NIIT\ESign\Mail\Mailable;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class SendSigningLink extends Mailable
{
    public function __construct(public Signer $signer, public Document $document)
    {
    }

    public function content(): Content
    {
        return new Content(
            view: 'esign::mails.signing-link'
        );
    }
}
