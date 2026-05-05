<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewEmployeeAccountNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Payroll System Account')
            ->greeting('Hello ' . $notifiable->name)
            ->line('An account has been created for you in the payroll system.')
            ->line('Your email: ' . $notifiable->email)
            ->line('Your temporary password: ' . $this->password)
            ->action('Login to Payroll System', url('/login'))
            ->line('Please change your password after logging in for security reasons.');
    }
}