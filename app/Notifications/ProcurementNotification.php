<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProcurementNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly ?string $url = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return array_values(array_filter([
            config('procurement.notifications.database', true) ? 'database' : null,
            config('procurement.notifications.mail', false) && $notifiable->email ? 'mail' : null,
        ]));
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'url' => $this->url];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject($this->title)->line($this->message)->when(
            $this->url,
            fn (MailMessage $mail) => $mail->action('Open procurement record', $this->url)
        );
    }
}
