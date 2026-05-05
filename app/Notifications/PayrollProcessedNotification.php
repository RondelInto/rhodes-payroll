<?php

namespace App\Notifications;

use App\Models\PayrollPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PayrollProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $period;

    public function __construct(PayrollPeriod $period)
    {
        $this->period = $period;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        if ($notifiable->role === 'admin') {
            return (new MailMessage)
                ->subject('Payroll Processed: ' . $this->period->name)
                ->greeting('Hello ' . $notifiable->name)
                ->line("Payroll for the period '{$this->period->name}' has been successfully processed.")
                ->line("Start Date: {$this->period->start_date->format('M d, Y')}")
                ->line("End Date: {$this->period->end_date->format('M d, Y')}")
                ->line("Pay Date: {$this->period->pay_date->format('M d, Y')}")
                ->action('View Payroll Details', url(route('payroll.show', $this->period)))
                ->line('Thank you for using Rhodes Payroll.');
        } else {
            // Regular employee
            return (new MailMessage)
                ->subject('Your Payslip is Ready – ' . $this->period->name)
                ->greeting('Hello ' . $notifiable->name)
                ->line("A payslip for the period '{$this->period->name}' has been generated and is now available.")
                ->line("Period: {$this->period->start_date->format('M d, Y')} – {$this->period->end_date->format('M d, Y')}")
                ->action('View My Payslips', url(route('my.payslips')))
                ->line('You can view and download your payslip from your employee dashboard.');
        }
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray($notifiable)
    {
        if ($notifiable->role === 'admin') {
            $url = route('payroll.show', $this->period);
        } else {
            $url = route('my.payslips');
        }

        return [
            'title' => 'Payroll Processed',
            'message' => "Payroll for period '{$this->period->name}' has been processed successfully.",
            'period_id' => $this->period->id,
            'url' => $url,
        ];
    }
}