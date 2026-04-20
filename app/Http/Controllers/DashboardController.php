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
        
        $recentTransactions = PayrollTransaction::with(['employee', 'period'])->latest()->take(5)->get();
        
        return view('dashboard', compact('totalEmployees', 'totalDepartments', 'pendingPayroll', 'monthlyPayrollTotal', 'departmentDistribution', 'recentTransactions'));
    }
}
