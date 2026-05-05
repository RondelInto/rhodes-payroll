<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\PayrollPeriod;
use App\Models\PayrollTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = Employee::where('status', 'active')->count();
        $totalDepartments = Department::count();
        $pendingPayroll = PayrollPeriod::where('status', 'draft')->count();
        $monthlyPayrollTotal = PayrollTransaction::whereMonth('created_at', now()->month)->sum('net_pay');

        $departmentDistribution = Department::withCount('employees')->get()
            ->map(fn($d) => ['name' => $d->name, 'count' => $d->employees_count]);

        // ✅ Get the most recent payroll period (latest by id or end_date)
        $latestPeriod = PayrollPeriod::latest('id')->first();

        if ($latestPeriod) {
            // ✅ Fetch all transactions for the latest period, ordered by employee name
            $recentTransactions = PayrollTransaction::with(['employee', 'period'])
                ->where('period_id', $latestPeriod->id)
                ->whereHas('employee')
                ->get()
                ->sortBy(fn($t) => $t->employee?->first_name . ' ' . $t->employee?->last_name);
        } else {
            $recentTransactions = collect();
        }

        return view('dashboard', compact(
            'totalEmployees',
            'totalDepartments',
            'pendingPayroll',
            'monthlyPayrollTotal',
            'departmentDistribution',
            'recentTransactions'
        ));
    }
}