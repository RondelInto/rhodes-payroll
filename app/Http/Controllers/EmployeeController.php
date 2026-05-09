<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\PayrollTransaction;
use App\Models\Attendance;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Notifications\NewEmployeeAccountNotification;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('department');

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

        $employee = Employee::create($data);

        $this->autoCreateUserAccount($employee);

        return redirect()->route('employees.index')->with('success', 'Employee created and user account created.');
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

    /**
     * Soft delete an employee (move to trash).
     * Also removes the employee as manager from any departments.
     */
    public function destroy(Employee $employee)
    {
        // Remove as manager from departments before soft deleting
        Department::where('manager_id', $employee->id)->update(['manager_id' => null]);

        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee moved to trash.');
    }

    public function restore($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->restore();
        return redirect()->route('employees.index')->with('success', 'Employee restored.');
    }

    /**
     * Permanently delete an employee (force delete).
     * Also removes the employee as manager from any departments and deletes related records.
     */
    public function forceDelete($id)
{
    try {
        $employee = Employee::withTrashed()->findOrFail($id);

        // 🔥 FORCE clear manager_id using raw DB (bypasses model scopes and events)
        \Illuminate\Support\Facades\DB::table('departments')
            ->where('manager_id', $employee->id)
            ->update(['manager_id' => null]);

        // Manually delete related records
        $employee->attendances()->forceDelete();
        $employee->payrollTransactions()->forceDelete();
        $employee->payrollAdjustments()->forceDelete();

        // Delete photo
        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }

        // Force delete the employee
        $employee->forceDelete();

        return redirect()->route('employees.index', ['trashed' => 'true'])
            ->with('success', 'Employee permanently deleted.');
    } catch (\Exception $e) {
        Log::error('Force delete failed: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Cannot delete employee: ' . $e->getMessage());
    }
}

    // ------------------------------------------------------------
    // Employee Self‑Service Methods
    // ------------------------------------------------------------

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

    public function myAttendance(Request $request)
    {
        $employee = Employee::where('email', auth()->user()->email)->first();
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'No employee record associated with your account.');
        }

        $periods = PayrollPeriod::orderBy('start_date', 'desc')->get();
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

    public function updatePassword(Request $request)
    {
        $employee = Employee::where('email', auth()->user()->email)->first();
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'No employee record associated with your account.');
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Check if the payroll period is already processed.
     * Returns a redirect response if locked, otherwise null.
     */
    private function checkPeriodNotProcessed($periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);
        if ($period->isProcessed()) {
            return redirect()->back()->with('error', 'Attendance cannot be modified because the payroll period has already been processed.');
        }
        return null;
    }

    // ==================== Employee Self‑Service Attendance Methods ====================

    public function storeSelfAttendance(Request $request)
    {
        $employee = Employee::where('email', auth()->user()->email)->first();
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'No employee record associated with your account.');
        }

        $request->validate([
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'nullable|in:present,absent,late,half-day,holiday',
        ]);

        $period = PayrollPeriod::where('start_date', '<=', $request->date)
                    ->where('end_date', '>=', $request->date)
                    ->first();

        if (!$period) {
            return back()->with('error', 'No payroll period found for the selected date.');
        }

        $locked = $this->checkPeriodNotProcessed($period->id);
        if ($locked) return $locked;

        $timeIn = $request->time_in;
        $timeOut = $request->time_out;
        $hoursWorked = 0;
        $lateHours = 0;
        $overtimeHours = 0;
        $status = $request->status ?? 'present';
        $shiftStart = $employee->shift_start ?? '09:00';

        if ($timeIn && $timeOut) {
            $in = Carbon::parse($request->date . ' ' . $timeIn);
            $out = Carbon::parse($request->date . ' ' . $timeOut);
            if ($out->lt($in)) $out->addDay();
            $hoursWorked = $in->diffInHours($out);
            $expectedStart = Carbon::parse($request->date . ' ' . $shiftStart);
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
                'employee_id' => $employee->id,
                'period_id' => $period->id,
                'date' => $request->date,
            ],
            [
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'hours_worked' => $hoursWorked,
                'late_hours' => $lateHours,
                'overtime_hours' => $overtimeHours,
                'status' => $status,
            ]
        );

        return back()->with('success', 'Attendance logged successfully.');
    }

    public function updateSelfAttendance(Request $request, Attendance $attendance)
    {
        $employee = Employee::where('email', auth()->user()->email)->first();
        if (!$employee || $attendance->employee_id != $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $locked = $this->checkPeriodNotProcessed($attendance->period_id);
        if ($locked) return $locked;

        $request->validate([
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'nullable|in:present,absent,late,half-day,holiday',
        ]);

        $shiftStart = $employee->shift_start ?? '09:00';
        $timeIn = $request->time_in ?? $attendance->time_in;
        $timeOut = $request->time_out ?? $attendance->time_out;
        $status = $request->status ?? $attendance->status;

        $hoursWorked = 0;
        $lateHours = 0;
        $overtimeHours = 0;

        if ($timeIn && $timeOut) {
            $in = Carbon::parse($attendance->date . ' ' . $timeIn);
            $out = Carbon::parse($attendance->date . ' ' . $timeOut);
            if ($out->lt($in)) $out->addDay();
            $hoursWorked = $in->diffInHours($out);
            $expectedStart = Carbon::parse($attendance->date . ' ' . $shiftStart);
            if ($in->gt($expectedStart)) {
                $lateHours = $expectedStart->diffInHours($in);
            }
            if ($hoursWorked > 8) {
                $overtimeHours = $hoursWorked - 8;
                $hoursWorked = 8;
            }
        }

        $attendance->update([
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'hours_worked' => $hoursWorked,
            'late_hours' => $lateHours,
            'overtime_hours' => $overtimeHours,
            'status' => $status,
        ]);

        return back()->with('success', 'Attendance updated.');
    }

    public function destroySelfAttendance(Attendance $attendance)
    {
        $employee = Employee::where('email', auth()->user()->email)->first();
        if (!$employee || $attendance->employee_id != $employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $locked = $this->checkPeriodNotProcessed($attendance->period_id);
        if ($locked) return $locked;

        $attendance->delete();
        return back()->with('success', 'Attendance record deleted.');
    }

    // ------------------------------------------------------------
    // Helper Methods
    // ------------------------------------------------------------

    private function generateEmployeeId()
    {
        $last = Employee::orderBy('id', 'desc')->first();
        $num = $last ? intval(substr($last->employee_id, 4)) + 1 : 1;
        return 'EMP-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }

    protected function autoCreateUserAccount(Employee $employee)
    {
        $existingUser = User::where('email', $employee->email)->first();
        if ($existingUser) {
            Log::info('User account already exists for email: ' . $employee->email);
            return;
        }

        $temporaryPassword = Str::random(10);

        $user = User::create([
            'name'     => $employee->full_name,
            'email'    => $employee->email,
            'password' => Hash::make($temporaryPassword),
            'role'     => 'user',
        ]);

        Log::info('User account created for employee: ' . $employee->email . ' with temporary password: ' . $temporaryPassword);

        //$user->notify(new NewEmployeeAccountNotification($temporaryPassword));
    }
}