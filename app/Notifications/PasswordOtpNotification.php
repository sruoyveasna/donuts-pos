<?php
// app/Notifications/PasswordOtpNotification.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public string $context = 'password_reset', // 'register' | 'password_reset'
        public int $minutes = 1
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $title = $this->context === 'register'
            ? 'Verify your registration'
            : 'Reset your password';

        return (new MailMessage)
            ->subject($title)
            ->line("Your OTP code is: {$this->code}")
            ->line("This code expires in about {$this->minutes} minute(s).")
            ->line('If you did not request this, you can ignore this email.');
    }
}
