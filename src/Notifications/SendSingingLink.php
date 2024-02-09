<?php

namespace NIIT\ESign\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NIIT\ESign\Models\Signer;

class SendSingingLink extends Notification
{
    use Queueable;

    public function __construct(protected Signer $signer)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Test')
            ->greeting('Greeting')
            ->line('The introduction to the notification.')
            ->action('Notification Action', $this->signer->signingUrl())
            ->line('Thank you for using our application!')
            ->attachMany([
                Attachment::fromStorageDisk($this->signer->document->document->disk, $this->signer->document->document->path),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
