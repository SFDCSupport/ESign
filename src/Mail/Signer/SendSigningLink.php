<?php
/*
 * @author : Anand Pilania
 * @mailto : Anand.Pilania@niit.com
 * @updated : 1/8/24, 8:45 PM
 */

namespace NIIT\ESign\Mail\Signer;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use NIIT\ESign\Mail\Mailable;
use NIIT\ESign\Models\Document;

class SendSigningLink extends Mailable
{
    public function __construct(public Document $document)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('esign::label.document_submitted_for_esign')
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'esign::mails.signing-link'
        );
    }
}
