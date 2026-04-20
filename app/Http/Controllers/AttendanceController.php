<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendanceImport;

class AttendanceController extends Controller
{
    public function index()
    {
        $periods = PayrollPeriod::orderBy('start_date', 'desc')->get();
        $selectedPeriod = request('period_id', $periods->first()?->id);
        $employees = Employee::where('status', 'active')->with('department')->get();

        $period = null;
        $dates = [];
        if ($selectedPeriod) {
            $period = PayrollPeriod::find($selectedPeriod);
            if ($period) {
                $dates = \Carbon\CarbonPeriod::create($period->start_date, $period->end_date);
            }
        }

        // Group attendance records by employee_id and date
        $attendanceData = [];
        if ($selectedPeriod) {
            $attendances = Attendance::where('period_id', $selectedPeriod)->get();
            foreach ($attendances as $att) {
                $empId = (int) $att->employee_id;
                $dateStr = $att->date->format('Y-m-d');
                $attendanceData[$empId][$dateStr] = $att;
            }
        }

        // Pre‑compute for each employee
        $employeeAttendance = [];
        foreach ($employees as $employee) {
            $employeeAttendance[$employee->id] = [
                'dates'   => $dates,
                'records' => $attendanceData[$employee->id] ?? [],
            ];
        }

        return view('attendance.index', compact('periods', 'selectedPeriod', 'employees', 'employeeAttendance', 'period'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period_id'   => 'required|exists:payroll_periods,id',
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|array',
            'time_in'     => 'array',
            'time_out'    => 'array',
            'status'      => 'array',
        ]);

        $periodId   = $request->period_id;
        $employeeId = $request->employee_id;
        $dates      = $request->date;
        $timeIns    = $request->time_in ?? [];
        $timeOuts   = $request->time_out ?? [];
        $statuses   = $request->status ?? [];

        // Fetch employee once (outside loop) to get shift_start
        $employee = Employee::find($employeeId);
        $shiftStart = $employee->shift_start ?? '09:00';

        if (empty($dates)) {
            return redirect()->back()->with('error', 'No dates found for the selected period.');
        }

        try {
            foreach ($dates as $index => $date) {
                $timeIn  = $timeIns[$index] ?? null;
                $timeOut = $timeOuts[$index] ?? null;
                $status  = $statuses[$index] ?? 'present';

                $hoursWorked = 0;
                $lateHours   = 0;
                $overtimeHours = 0;

                if ($timeIn && $timeOut) {
                    $in  = \Carbon\Carbon::parse($date . ' ' . $timeIn);
                    $out = \Carbon\Carbon::parse($date . ' ' . $timeOut);
                    if ($out->lt($in)) {
                        $out->addDay();
                    }

                    $hoursWorked = $in->diffInHours($out);
                    $expectedStart = \Carbon\Carbon::parse($date . ' ' . $shiftStart);
                    if ($in->gt($expectedStart)) {
                        $lateHours = $expectedStart->diffInHours($in);
                    }
                    if ($hoursWorked > 8) {
                        $overtimeHours = $hoursWorked - 8;
                        $hoursWorked = 8;
                    }
                }

                Attendance::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'period_id'   => $periodId,
                        'date'        => $date,
                    ],
                    [
                        'time_in'        => $timeIn,
                        'time_out'       => $timeOut,
                        'hours_worked'   => $hoursWorked,
                        'late_hours'     => $lateHours,
                        'overtime_hours' => $overtimeHours,
                        'status'         => $status,
                    ]
                );
            }

            return redirect()->back()->with('success', 'Attendance saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save attendance: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
            'csv_file'  => 'required|file|mimes:csv,txt',
        ]);

        Excel::import(new AttendanceImport($request->period_id), $request->file('csv_file'));
        return redirect()->back()->with('success', 'Attendance imported successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->back()->with('success', 'Attendance record deleted.');
    }
}