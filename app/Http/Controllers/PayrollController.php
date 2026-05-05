<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Models\PayrollTransaction;
use App\Models\CompanySetting;
use App\Models\PayrollAdjustment;
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

    public function payslipHtml(PayrollTransaction $transaction)
    {
        $company = CompanySetting::pluck('value', 'key')->toArray();
        return view('payroll.payslip-printable', compact('transaction', 'company'));
    }

    public function payslip(PayrollTransaction $transaction)
    {
        $company = CompanySetting::pluck('value', 'key')->toArray();
        return view('payroll.payslip-printable', compact('transaction', 'company'));
    }

    public function downloadPayslip(PayrollTransaction $transaction)
    {
        $company = CompanySetting::pluck('value', 'key')->toArray();
        $pdf = Pdf::loadView('payroll.payslip-printable', compact('transaction', 'company'));
        return $pdf->download("payslip-{$transaction->employee->employee_id}-{$transaction->period->name}.pdf");
    }

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

    // ==================== One‑off Adjustments ====================
    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_id'   => 'required|exists:payroll_periods,id',
            'type'        => 'required|in:bonus,deduction',
            'amount'      => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        PayrollAdjustment::create($request->all());

        return back()->with('success', 'Adjustment added.');
    }

    public function updateAdjustment(Request $request, PayrollAdjustment $adjustment)
    {
        $request->validate([
            'type'        => 'required|in:bonus,deduction',
            'amount'      => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $adjustment->update($request->only(['type', 'amount', 'description']));

        return back()->with('success', 'Adjustment updated.');
    }

    public function destroyAdjustment(PayrollAdjustment $adjustment)
    {
        $adjustment->delete();
        return back()->with('success', 'Adjustment deleted.');
    }
}