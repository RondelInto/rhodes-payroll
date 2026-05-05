<?php

namespace App\Jobs;

use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PayrollAdjustment;
use App\Models\CustomAllowance;
use App\Models\CustomDeduction;
use App\Models\CompanySetting;
use App\Models\User;
use App\Models\PayrollTransaction;
use App\Services\PayrollCalculationService;
use App\Notifications\PayrollProcessedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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
        // Acquire a lock for this period (10 minutes timeout)
        $lock = Cache::lock("payroll_processing_{$this->period->id}", 600);
        if (!$lock->get()) {
            Log::warning("Another payroll job is already running for period {$this->period->id}. Skipping.");
            return;
        }

        try {
            // Double‑check status after acquiring lock (prevent redundant runs)
            if ($this->period->fresh()->status === 'processed') {
                Log::info("Payroll period {$this->period->id} is already processed. Job skipped.");
                return;
            }

            $startTime = microtime(true);
            Log::info('Starting payroll processing for period: ' . $this->period->id);

            // 1. Get active employees
            $employees = Employee::where('status', 'active')->get();
            if ($employees->isEmpty()) {
                Log::warning('No active employees found.');
                return;
            }

            $employeeIds = $employees->pluck('id');

            // 2. Pre‑load all attendance records for this period
            $attendances = Attendance::whereIn('employee_id', $employeeIds)
                ->where('period_id', $this->period->id)
                ->get()
                ->groupBy('employee_id');

            // 3. Pre‑load adjustments
            $adjustments = PayrollAdjustment::whereIn('employee_id', $employeeIds)
                ->where('period_id', $this->period->id)
                ->get()
                ->groupBy('employee_id');

            // 4. Cache global settings
            $allowances = Cache::remember('custom_allowances_active', 3600, function () {
                return CustomAllowance::where('is_active', true)->get();
            });
            $deductions = Cache::remember('custom_deductions_active', 3600, function () {
                return CustomDeduction::where('is_active', true)->get();
            });

            $settings = [
                'working_hours_per_day' => CompanySetting::getValue('working_hours_per_day', 8),
                'overtime_rate' => CompanySetting::getValue('overtime_rate_multiplier', 1.25),
                'late_deduction_per_hour' => CompanySetting::getValue('late_deduction_per_hour', 50),
                'holiday_rate' => CompanySetting::getValue('holiday_rate_multiplier', 2.0),
                'night_differential_rate' => CompanySetting::getValue('night_differential_rate', 1.10),
            ];

            // 5. Process in chunks
            $chunkSize = 200;
            $chunks = $employees->chunk($chunkSize);

            foreach ($chunks as $chunk) {
                DB::transaction(function () use ($chunk, $attendances, $adjustments, $allowances, $deductions, $settings, $service) {
                    $transactions = [];

                    foreach ($chunk as $employee) {
                        $empAttendance = $attendances[$employee->id] ?? collect();
                        $empAdjustments = $adjustments[$employee->id] ?? collect();

                        $calculation = $service->calculateEmployeePayrollWithData(
                            $employee,
                            $this->period,
                            $empAttendance,
                            $empAdjustments,
                            $allowances,
                            $deductions,
                            $settings
                        );

                        // JSON encode array fields for database
                        $calculation['allowances'] = json_encode($calculation['allowances'] ?? []);
                        $calculation['other_deductions'] = json_encode($calculation['other_deductions'] ?? []);

                        $transactions[] = array_merge($calculation, [
                            'employee_id' => $employee->id,
                            'period_id' => $this->period->id,
                            'processed_date' => now(),
                            'processed_by' => $this->userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Bulk upsert
                    PayrollTransaction::upsert(
                        $transactions,
                        ['employee_id', 'period_id'],
                        ['basic_pay', 'overtime_pay', 'holiday_pay', 'allowances', 'gross_pay',
                         'sss_contribution', 'philhealth_contribution', 'pagibig_contribution',
                         'withholding_tax', 'other_deductions', 'total_deductions', 'net_pay',
                         'processed_date', 'processed_by', 'updated_at']
                    );
                });
            }

            // 6. Mark period as processed
            $this->period->update(['status' => 'processed']);

            // 7. Delete old notifications for this period to avoid duplicates
            DB::table('notifications')
                ->where('type', 'App\Notifications\PayrollProcessedNotification')
                ->whereRaw('JSON_EXTRACT(data, "$.period_id") = ?', [$this->period->id])
                ->delete();

            // 8. Notify all users
            $users = User::all();
            foreach ($users->chunk(100) as $chunk) {
                foreach ($chunk as $user) {
                    $user->notify(new PayrollProcessedNotification($this->period));
                }
            }

            $duration = round(microtime(true) - $startTime, 2);
            Log::info("Payroll processing completed for period {$this->period->id} in {$duration} seconds.");

        } finally {
            $lock->release();
        }
    }
}