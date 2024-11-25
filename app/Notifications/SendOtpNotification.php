<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $otp;

    /**
     * Create a new notification instance.
     *
     * @param string $otp
     */
    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Specify the delivery channel (email)
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your OTP Code')
            ->line('Use the following OTP to complete your request:')
            ->line($this->otp)
            ->line('This OTP is valid for 15 minutes.')
            ->line('If you did not request this, please ignore this email.');
    }
}
