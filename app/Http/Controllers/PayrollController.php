<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Models\PayrollTransaction;
use App\Models\CompanySetting;
use App\Services\PayrollCalculationService;
use App\Jobs\ProcessPayrollJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollCalculationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index()
    {
        $periods = PayrollPeriod::withCount('transactions')->get();
        return view('payroll.index', compact('periods'));
    }

    public function process(PayrollPeriod $period)
    {
        if ($period->status !== 'draft') {
            return back()->with('error', 'Payroll period is already processed');
        }

        // Dispatch the job to the queue
        ProcessPayrollJob::dispatch($period, auth()->id());

        return back()->with('success', 'Payroll processing has been queued. You will be notified when complete.');
    }

    public function show(PayrollPeriod $period)
    {
        $transactions = PayrollTransaction::with('employee.department')
            ->where('period_id', $period->id)
            ->get();

        return view('payroll.show', compact('period', 'transactions'));
    }

    // Returns HTML for modal payslip (used by AJAX)
    public function payslipHtml(PayrollTransaction $transaction)
    {
        $company = CompanySetting::pluck('value', 'key')->toArray();
        return view('payroll.payslip-printable', compact('transaction', 'company'));
    }

    // Returns printable HTML (full page)
    public function payslip(PayrollTransaction $transaction)
    {
        $company = CompanySetting::pluck('value', 'key')->toArray();
        return view('payroll.payslip-printable', compact('transaction', 'company'));
    }

    // Download PDF
    public function downloadPayslip(PayrollTransaction $transaction)
    {
        $company = CompanySetting::pluck('value', 'key')->toArray();
        $pdf = Pdf::loadView('payroll.payslip-printable', compact('transaction', 'company'));
        return $pdf->download("payslip-{$transaction->employee->employee_id}-{$transaction->period->name}.pdf");
    }

    // Regenerate a single transaction (only for draft periods)
    public function regenerate(PayrollTransaction $transaction)
    {
        $period = $transaction->period;
        if ($period->status !== 'draft') {
            return back()->with('error', 'Cannot regenerate for processed period');
        }
        $calculation = $this->payrollService->calculateEmployeePayroll($transaction->employee, $period);
        $transaction->update($calculation);
        return back()->with('success', 'Transaction regenerated');
    }
}