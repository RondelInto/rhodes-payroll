<?php


namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollTransaction;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollSummaryExport;
use App\Exports\EmployeeEarningsExport;
use App\Exports\DeductionsExport;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $periods = PayrollPeriod::where('status', 'processed')->orWhere('status', 'paid')->get();
        $departments = Department::all();
        return view('reports.index', compact('periods', 'departments'));
    }

    public function payrollSummary(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        return Excel::download(
            new PayrollSummaryExport($request->period_id, $request->department_id),
            'payroll_summary_' . now()->format('Ymd_His') . '.csv'
        );
    }

    public function employeeEarnings(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_id' => 'nullable|exists:payroll_periods,id',
        ]);

        return Excel::download(
            new EmployeeEarningsExport($request->employee_id, $request->period_id),
            'employee_earnings_' . now()->format('Ymd_His') . '.csv'
        );
    }

    public function deductionsReport(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
        ]);

        return Excel::download(
            new DeductionsExport($request->period_id),
            'deductions_report_' . now()->format('Ymd_His') . '.csv'
        );
    }
}