<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\PayrollTransaction;
use App\Models\Attendance;
use App\Models\PayrollPeriod;  // added for period dropdown
use App\Http\Requests\EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('department');

        // Show only soft‑deleted records if 'trashed' parameter is present
        if ($request->has('trashed')) {
            $query->onlyTrashed();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->paginate(15);
        $departments = Department::all();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function edit(Employee $employee)
    {
        return response()->json($employee);
    }

    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();
        $data['employee_id'] = $this->generateEmployeeId();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employee-photos', 'public');
            Log::info('Photo stored at: ' . $data['photo']);
        }

        Employee::create($data);
        return redirect()->route('employees.index')->with('success', 'Employee created.');
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
            }
            $data['photo'] = $request->file('photo')->store('employee-photos', 'public');
            Log::info('Photo updated to: ' . $data['photo']);
        }

        $employee->update($data);
        return redirect()->route('employees.index')->with('success', 'Employee updated.');
    }

    // Soft delete
    public function destroy(Employee $employee)
    {
        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee moved to trash.');
    }

    // Restore soft deleted employee
    public function restore($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->restore();
        return redirect()->route('employees.index')->with('success', 'Employee restored.');
    }

    // Permanently delete (force delete)
    public function forceDelete($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }
        $employee->forceDelete();
        return redirect()->route('employees.index')->with('success', 'Employee permanently deleted.');
    }

    // ─────────────────────────────────────────────────────────
    // Employee Self‑Service Methods
    // ─────────────────────────────────────────────────────────

    public function myPayslips()
    {
        $employee = Employee::where('email', auth()->user()->email)->first();
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'No employee record associated with your account.');
        }
        $transactions = PayrollTransaction::where('employee_id', $employee->id)
            ->with('period')
            ->get();
        return view('employees.my-payslips', compact('transactions'));
    }

    /**
     * Show employee's own attendance, grouped by payroll period.
     */
    public function myAttendance(Request $request)
    {
        $employee = Employee::where('email', auth()->user()->email)->first();
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'No employee record associated with your account.');
        }

        // Get all payroll periods that have attendance for this employee
        $periods = PayrollPeriod::whereHas('attendances', function ($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })->orderBy('start_date', 'desc')->get();

        $selectedPeriod = $request->get('period_id', $periods->first()?->id);

        $attendances = collect();
        $period = null;
        if ($selectedPeriod) {
            $period = PayrollPeriod::find($selectedPeriod);
            if ($period) {
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->where('period_id', $selectedPeriod)
                    ->orderBy('date', 'asc')
                    ->get();
            }
        }

        return view('employees.my-attendance', compact('periods', 'selectedPeriod', 'attendances', 'period', 'employee'));
    }

    public function myProfile()
    {
        $employee = Employee::where('email', auth()->user()->email)->first();
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'No employee record associated with your account.');
        }
        return view('employees.my-profile', compact('employee'));
    }

    public function updateProfile(Request $request)
    {
        $employee = Employee::where('email', auth()->user()->email)->first();
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'No employee record associated with your account.');
        }
        $request->validate([
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'zip_code' => 'nullable|string',
        ]);
        $employee->update($request->only(['phone', 'address', 'city', 'province', 'zip_code']));
        return back()->with('success', 'Profile updated.');
    }

    private function generateEmployeeId()
    {
        $last = Employee::orderBy('id', 'desc')->first();
        $num = $last ? intval(substr($last->employee_id, 4)) + 1 : 1;
        return 'EMP-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}