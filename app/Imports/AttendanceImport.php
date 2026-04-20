<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class AttendanceImport implements ToModel, WithHeadingRow
{
    protected $periodId;

    public function __construct($periodId)
    {
        $this->periodId = $periodId;
    }

    public function model(array $row)
    {
        $timeIn = $row['time_in'] ? Carbon::parse($row['time_in']) : null;
        $timeOut = $row['time_out'] ? Carbon::parse($row['time_out']) : null;
        $hoursWorked = 0;
        $lateHours = 0;
        $overtimeHours = 0;

        if ($timeIn && $timeOut) {
            $hoursWorked = $timeIn->diffInHours($timeOut);
            $employee = Employee::find($row['employee_id']);
            $shiftStart = $employee->shift_start ?? '09:00';
            $expectedStart = Carbon::parse($row['date'] . ' ' . $shiftStart);
            if ($timeIn->gt($expectedStart)) {
                $lateHours = $expectedStart->diffInHours($timeIn);
            }
            if ($hoursWorked > 8) {
                $overtimeHours = $hoursWorked - 8;
                $hoursWorked = 8;
            }
        }

        return new Attendance([
            'employee_id' => $row['employee_id'],
            'period_id' => $this->periodId,
            'date' => $row['date'],
            'time_in' => $row['time_in'],
            'time_out' => $row['time_out'],
            'hours_worked' => $hoursWorked,
            'late_hours' => $lateHours,
            'overtime_hours' => $overtimeHours,
            'status' => $row['status'] ?? 'present',
        ]);
    }
}