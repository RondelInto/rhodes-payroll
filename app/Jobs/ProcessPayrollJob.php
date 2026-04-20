<?php

namespace App\Jobs;

use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Models\PayrollTransaction;
use App\Models\User;
use App\Services\PayrollCalculationService;
use App\Notifications\PayrollProcessedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $period;
    protected $userId;

    public function __construct(PayrollPeriod $period, $userId)
    {
        $this->period = $period;
        $this->userId = $userId;
    }

    public function handle(PayrollCalculationService $service)
    {
        DB::transaction(function () use ($service) {
            $employees = Employee::where('status', 'active')->get();
            foreach ($employees as $employee) {
                $calculation = $service->calculateEmployeePayroll($employee, $this->period);
                PayrollTransaction::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'period_id'   => $this->period->id,
                    ],
                    array_merge($calculation, [
                        'processed_date' => now(),
                        'processed_by'   => $this->userId,
                    ])
                );
            }
            $this->period->update(['status' => 'processed']);
        });

        // Send notification to ALL users (admin + regular employees)
        $users = User::all();
        Log::info('Sending payroll notifications to ' . $users->count() . ' users');
        foreach ($users as $user) {
            $user->notify(new PayrollProcessedNotification($this->period));
        }
    }
}