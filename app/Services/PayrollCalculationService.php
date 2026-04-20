<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Attendance;
use App\Models\CustomDeduction;
use App\Models\CustomAllowance;
use App\Models\CompanySetting;
use Carbon\Carbon;

class PayrollCalculationService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = [
            'working_hours_per_day' => (float) CompanySetting::getValue('working_hours_per_day', 8),
            'overtime_rate' => (float) CompanySetting::getValue('overtime_rate_multiplier', 1.25),
            'late_deduction_per_hour' => (float) CompanySetting::getValue('late_deduction_per_hour', 50),
            'holiday_rate' => (float) CompanySetting::getValue('holiday_rate_multiplier', 2.0),
            'night_differential_rate' => (float) CompanySetting::getValue('night_differential_rate', 1.10),
        ];
    }

    public function calculateEmployeePayroll(Employee $employee, PayrollPeriod $period)
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->where('period_id', $period->id)
            ->get();

        $totalWorkingDays = $this->getWorkingDays($period);
        $dailyRate = $employee->basic_salary / max($totalWorkingDays, 1);
        $daysPresent = $attendances->whereIn('status', ['present', 'late', 'half-day'])->count();

        // ==================== GUARD: No attendance days → zero pay, zero deductions ====================
        if ($daysPresent == 0) {
            return [
                'basic_pay' => 0,
                'overtime_pay' => 0,
                'holiday_pay' => 0,
                'allowances' => [],
                'gross_pay' => 0,
                'sss_contribution' => 0,
                'philhealth_contribution' => 0,
                'pagibig_contribution' => 0,
                'withholding_tax' => 0,
                'other_deductions' => [],
                'total_deductions' => 0,
                'net_pay' => 0,
            ];
        }

        $basicPay = $daysPresent * $dailyRate;

        // Late deductions
        $totalLateHours = $attendances->sum('late_hours');
        $lateDeduction = $totalLateHours * $this->settings['late_deduction_per_hour'];
        $basicPay = max(0, $basicPay - $lateDeduction);

        // Overtime
        $totalOvertimeHours = $attendances->sum('overtime_hours');
        $hourlyRate = $employee->basic_salary / ($totalWorkingDays * $this->settings['working_hours_per_day']);
        $overtimePay = $totalOvertimeHours * $hourlyRate * $this->settings['overtime_rate'];

        // Holiday pay
        $holidayCount = $attendances->where('status', 'holiday')->count();
        $holidayPay = $holidayCount * $dailyRate * $this->settings['holiday_rate'];

        // Night differential
        $totalNightDiffHours = 0;
        foreach ($attendances as $att) {
            $totalNightDiffHours += $this->calculateNightDiffHours($att->time_in, $att->time_out);
        }
        $nightDifferentialPay = $totalNightDiffHours * $hourlyRate * ($this->settings['night_differential_rate'] - 1);

        // Allowances
        $allowancesList = CustomAllowance::where('is_active', true)->get();
        $allowances = [];
        $totalAllowances = 0;
        foreach ($allowancesList as $a) {
            $amount = $a->type === 'fixed' ? $a->amount : ($employee->basic_salary * $a->amount / 100);
            $allowances[$a->name] = $amount;
            $totalAllowances += $amount;
        }

        $grossPay = $basicPay + $overtimePay + $holidayPay + $nightDifferentialPay + $totalAllowances;

        // Statutory contributions (based on full monthly salary – you may later pro‑rate)
        $sss = $this->computeSSS($employee->basic_salary);
        $philhealth = $this->computePhilHealth($employee->basic_salary);
        $pagibig = $this->computePagIBIG($employee->basic_salary);

        $taxableIncome = $grossPay - $sss - $philhealth - $pagibig;
        $withholdingTax = $this->computeTax($taxableIncome);

        // Custom deductions
        $deductionsList = CustomDeduction::where('is_active', true)->get();
        $otherDeductions = [];
        $totalOther = 0;
        foreach ($deductionsList as $d) {
            $amount = $d->type === 'fixed' ? $d->amount : ($employee->basic_salary * $d->amount / 100);
            $otherDeductions[$d->name] = $amount;
            $totalOther += $amount;
        }

        $totalDeductions = $sss + $philhealth + $pagibig + $withholdingTax + $totalOther;
        $netPay = $grossPay - $totalDeductions;

        // ==================== SAFETY: If net pay is negative, zero out deductions ====================
        if ($netPay < 0) {
            return [
                'basic_pay' => round($basicPay, 2),
                'overtime_pay' => round($overtimePay, 2),
                'holiday_pay' => round($holidayPay, 2),
                'allowances' => $allowances,
                'gross_pay' => round($grossPay, 2),
                'sss_contribution' => 0,
                'philhealth_contribution' => 0,
                'pagibig_contribution' => 0,
                'withholding_tax' => 0,
                'other_deductions' => [],
                'total_deductions' => 0,
                'net_pay' => round($grossPay, 2),
            ];
        }

        return [
            'basic_pay' => round($basicPay, 2),
            'overtime_pay' => round($overtimePay, 2),
            'holiday_pay' => round($holidayPay, 2),
            'allowances' => $allowances,
            'gross_pay' => round($grossPay, 2),
            'sss_contribution' => round($sss, 2),
            'philhealth_contribution' => round($philhealth, 2),
            'pagibig_contribution' => round($pagibig, 2),
            'withholding_tax' => round($withholdingTax, 2),
            'other_deductions' => $otherDeductions,
            'total_deductions' => round($totalDeductions, 2),
            'net_pay' => round($netPay, 2),
        ];
    }

    /**
     * Calculate night differential hours for a given shift (10 PM - 6 AM)
     */
    private function calculateNightDiffHours($timeIn, $timeOut)
    {
        if (!$timeIn || !$timeOut) {
            return 0;
        }

        $start = Carbon::parse($timeIn);
        $end = Carbon::parse($timeOut);
        
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        $nightStart = (clone $start)->setTime(22, 0);
        $nightEnd   = (clone $start)->setTime(6, 0)->addDay();
        
        if ($end->lte($nightStart) || $start->gte($nightEnd)) {
            return 0;
        }
        
        $overlapStart = $start->max($nightStart);
        $overlapEnd   = $end->min($nightEnd);
        
        return round($overlapStart->diffInHours($overlapEnd, true), 2);
    }

    private function getWorkingDays(PayrollPeriod $period)
    {
        $start = Carbon::parse($period->start_date);
        $end = Carbon::parse($period->end_date);
        $days = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (!$d->isSaturday() && !$d->isSunday()) $days++;
        }
        return $days;
    }

    private function computeSSS($salary)
    {
        $brackets = [
            ['max' => 4249.99, 'val' => 450], ['max' => 4749.99, 'val' => 495],
            ['max' => 5249.99, 'val' => 540], ['max' => 5749.99, 'val' => 585],
            ['max' => 6249.99, 'val' => 630], ['max' => 6749.99, 'val' => 675],
            ['max' => 7249.99, 'val' => 720], ['max' => 7749.99, 'val' => 765],
            ['max' => 8249.99, 'val' => 810], ['max' => 8749.99, 'val' => 855],
            ['max' => 9249.99, 'val' => 900], ['max' => 9749.99, 'val' => 945],
            ['max' => 10249.99, 'val' => 990], ['max' => 10749.99, 'val' => 1035],
            ['max' => 11249.99, 'val' => 1080], ['max' => 11749.99, 'val' => 1125],
            ['max' => 12249.99, 'val' => 1170], ['max' => 12749.99, 'val' => 1215],
            ['max' => 13249.99, 'val' => 1260], ['max' => 13749.99, 'val' => 1305],
            ['max' => 14249.99, 'val' => 1350], ['max' => 14749.99, 'val' => 1395],
            ['max' => 15249.99, 'val' => 1440], ['max' => 15749.99, 'val' => 1485],
            ['max' => 16249.99, 'val' => 1530], ['max' => 16749.99, 'val' => 1575],
            ['max' => 17249.99, 'val' => 1620], ['max' => 17749.99, 'val' => 1665],
            ['max' => 18249.99, 'val' => 1710], ['max' => 18749.99, 'val' => 1755],
            ['max' => 19249.99, 'val' => 1800], ['max' => 19749.99, 'val' => 1845],
            ['max' => 20249.99, 'val' => 1890], ['max' => 20749.99, 'val' => 1935],
            ['max' => 21249.99, 'val' => 1980], ['max' => 21749.99, 'val' => 2025],
            ['max' => 22249.99, 'val' => 2070], ['max' => 22749.99, 'val' => 2115],
            ['max' => 23249.99, 'val' => 2160], ['max' => 23749.99, 'val' => 2205],
            ['max' => 24249.99, 'val' => 2250], ['max' => 24749.99, 'val' => 2295],
            ['max' => 25249.99, 'val' => 2340], ['max' => 25749.99, 'val' => 2385],
            ['max' => 26249.99, 'val' => 2430], ['max' => 26749.99, 'val' => 2475],
            ['max' => 27249.99, 'val' => 2520], ['max' => 27749.99, 'val' => 2565],
            ['max' => 28249.99, 'val' => 2610], ['max' => 28749.99, 'val' => 2655],
            ['max' => 29249.99, 'val' => 2700], ['max' => 29749.99, 'val' => 2745],
            ['max' => PHP_FLOAT_MAX, 'val' => 4000],
        ];
        foreach ($brackets as $b) if ($salary <= $b['max']) return $b['val'];
        return 4000;
    }

    private function computePhilHealth($salary)
    {
        $premium = $salary * 0.05 / 2;
        return min(max($premium, 500), 5000);
    }

    private function computePagIBIG($salary)
    {
        $rate = $salary <= 1500 ? 0.01 : 0.02;
        return min($salary * $rate, 100);
    }

    private function computeTax($monthly)
    {
        $annual = $monthly * 12;
        $brackets = [
            ['max' => 250000, 'base' => 0, 'rate' => 0, 'excess' => 0],
            ['max' => 400000, 'base' => 0, 'rate' => 0.15, 'excess' => 250000],
            ['max' => 800000, 'base' => 22500, 'rate' => 0.20, 'excess' => 400000],
            ['max' => 2000000, 'base' => 102500, 'rate' => 0.25, 'excess' => 800000],
            ['max' => 8000000, 'base' => 402500, 'rate' => 0.30, 'excess' => 2000000],
            ['max' => PHP_FLOAT_MAX, 'base' => 2202500, 'rate' => 0.35, 'excess' => 8000000],
        ];
        foreach ($brackets as $b) {
            if ($annual <= $b['max']) {
                $tax = $b['base'] + ($annual - $b['excess']) * $b['rate'];
                return round($tax / 12, 2);
            }
        }
        return 0;
    }
}