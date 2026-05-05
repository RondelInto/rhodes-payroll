<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\Department;
use App\Models\Employee;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollSummaryExport;
use App\Exports\EmployeeEarningsExport;
use App\Exports\DeductionsExport;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $periods = PayrollPeriod::where('status', '!=', 'draft')->get();
        $departments = Department::all();
        return view('reports.index', compact('periods', 'departments'));
    }

    public function payrollSummary(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
        ]);
        return Excel::download(new PayrollSummaryExport($request->period_id, $request->department_id), 'payroll_summary.csv');
    }

    public function employeeEarnings(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);
        return Excel::download(new EmployeeEarningsExport($request->employee_id, $request->period_id), 'employee_earnings.csv');
    }

    public function deductionsReport(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
        ]);
        return Excel::download(new DeductionsExport($request->period_id), 'deductions_report.csv');
    }

    /**
     * Export bank file (CSV) for a given payroll period.
     */
    public function bankFileExport(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
        ]);

        $period = PayrollPeriod::findOrFail($request->period_id);
        $transactions = \App\Models\PayrollTransaction::where('period_id', $period->id)
            ->with('employee')
            ->get();

        if ($transactions->isEmpty()) {
            return back()->with('error', 'No transactions found for this period.');
        }

        $filename = 'bank_export_' . $period->name . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            // CSV headers – adjust to your bank's required format
            fputcsv($file, ['Employee ID', 'Employee Name', 'Account Number', 'Bank Code', 'Net Pay', 'Period']);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->employee->employee_id,
                    $t->employee->full_name,
                    $t->employee->bank_account ?? '',
                    $t->employee->bank_code ?? '',
                    number_format($t->net_pay, 2, '.', ''),
                    $t->period->name,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}