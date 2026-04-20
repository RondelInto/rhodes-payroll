<?php

namespace App\Notifications;

use App\Models\PayrollPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class PayrollProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $period;

    public function __construct(PayrollPeriod $period)
    {
        $this->period = $period;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        // Different URL based on user role
        if ($notifiable->role === 'admin') {
            $url = url(route('payroll.show', $this->period));
        } else {
            // Regular employees go to their own payslips page
            $url = url(route('my.payslips'));
        }

        return [
            'title' => 'Payroll Processed',
            'message' => "Payroll for period '{$this->period->name}' has been processed successfully.",
            'period_id' => $this->period->id,
            'url' => $url,
        ];
    }
}