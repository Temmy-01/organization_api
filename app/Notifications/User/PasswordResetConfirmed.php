<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetConfirmed extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $callbackContactUrl
     * @return void
     */
    public function __construct(string $callbackContactUrl)
    {
        $this->callbackContactUrl = $callbackContactUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
                    ->line('You have successfully reset your password.')
                    ->line('Thank you for using our application!');
    }
}
