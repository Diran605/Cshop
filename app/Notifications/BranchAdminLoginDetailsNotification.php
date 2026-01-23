<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BranchAdminLoginDetailsNotification extends Notification
{
    use Queueable;

    public function __construct(public string $tempPassword)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Branch Admin Login Details')
            ->greeting('Hello ' . ($notifiable->name ?? ''))
            ->line('An account has been created for you.')
            ->line('Email: ' . ($notifiable->email ?? ''))
            ->line('Temporary Password: ' . $this->tempPassword)
            ->action('Login', route('login'))
            ->line('After logging in, you can use the "Forgot Password" link to change your password.');
    }
}
