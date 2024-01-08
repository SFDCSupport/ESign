<?php

namespace NIIT\ESign\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable as Base;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

abstract class Mailable extends Base
{
    use Queueable, SerializesModels;

    private ?string $messageId = 'custom-message-id@example.com';

    private ?array $references = ['previous-message@example.com'];

    public function headers(): Headers
    {
        return new Headers(
            messageId: $this->messageId,
            references: $this->references,
            text: [
                'X-Custom-Header' => 'NIIT ESign',
            ],
        );
    }
}
