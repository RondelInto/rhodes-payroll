<?php

use App\Http\Controllers\{
    DashboardController, EmployeeController, DepartmentController,
    AttendanceController, PayrollPeriodController, PayrollController,
    ReportController, SettingController, AuditLogController, NotificationController
};
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // ==================== ROUTES FOR ALL AUTHENTICATED USERS ====================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Employee Self‑Service (accessible by all)
    Route::get('/my-payslips', [EmployeeController::class, 'myPayslips'])->name('my.payslips');
    Route::get('/my-attendance', [EmployeeController::class, 'myAttendance'])->name('my.attendance');
    Route::get('/my-profile', [EmployeeController::class, 'myProfile'])->name('my.profile');
    Route::put('/my-profile', [EmployeeController::class, 'updateProfile'])->name('my.profile.update');

    // ==================== NOTIFICATION API (for bell dropdown - AJAX) ====================
    Route::get('/notifications/api', function () {
        return auth()->user()->unreadNotifications;
    })->name('notifications.fetch');

    Route::post('/notifications/api/{id}/read', function ($id) {
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    })->name('notifications.mark-read');

    Route::post('/notifications/api/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.read-all');

    // ==================== NOTIFICATION HISTORY PAGE (normal form submissions) ====================
    Route::get('/notifications/history', [NotificationController::class, 'index'])->name('notifications.history');
    Route::post('/notifications/history/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read-page');
    Route::post('/notifications/history/{id}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.mark-unread');
    Route::delete('/notifications/history/{id}/delete', [NotificationController::class, 'delete'])->name('notifications.delete');
    Route::post('/notifications/history/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // ==================== ADMIN‑ONLY ROUTES ====================
    Route::middleware(['role:admin'])->group(function () {
        // Employees
        Route::resource('employees', EmployeeController::class);

        // Departments
        Route::resource('departments', DepartmentController::class);

        // Attendance
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('attendance/import', [AttendanceController::class, 'import'])->name('attendance.import');
        Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

        // Payroll Periods – calendar route MUST come BEFORE resource route
        Route::get('periods/calendar', [PayrollPeriodController::class, 'calendar'])->name('periods.calendar');
        Route::resource('periods', PayrollPeriodController::class);

        // Payroll Processing
        Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::post('payroll/{period}/process', [PayrollController::class, 'process'])->name('payroll.process');
        Route::get('payroll/{period}', [PayrollController::class, 'show'])->name('payroll.show');
        Route::post('payroll/transaction/{transaction}/regenerate', [PayrollController::class, 'regenerate'])->name('payroll.regenerate');
        Route::get('payroll/transaction/{transaction}/payslip-html', [PayrollController::class, 'payslipHtml']);
        Route::get('payroll/transaction/{transaction}/download', [PayrollController::class, 'downloadPayslip'])->name('payroll.download');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/payroll-summary', [ReportController::class, 'payrollSummary'])->name('reports.payroll-summary');
        Route::post('reports/employee-earnings', [ReportController::class, 'employeeEarnings'])->name('reports.employee-earnings');
        Route::post('reports/deductions', [ReportController::class, 'deductionsReport'])->name('reports.deductions');

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings/company', [SettingController::class, 'updateCompany'])->name('settings.company');
        Route::post('settings/payroll', [SettingController::class, 'updatePayroll'])->name('settings.payroll');

        // Custom Deductions & Allowances (full CRUD)
        Route::post('/settings/deductions', [SettingController::class, 'storeDeduction'])->name('settings.deductions.store');
        Route::put('/settings/deductions/{deduction}', [SettingController::class, 'updateDeduction'])->name('settings.deductions.update');
        Route::delete('/settings/deductions/{deduction}', [SettingController::class, 'destroyDeduction'])->name('settings.deductions.destroy');

        Route::post('/settings/allowances', [SettingController::class, 'storeAllowance'])->name('settings.allowances.store');
        Route::put('/settings/allowances/{allowance}', [SettingController::class, 'updateAllowance'])->name('settings.allowances.update');
        Route::delete('/settings/allowances/{allowance}', [SettingController::class, 'destroyAllowance'])->name('settings.allowances.destroy');

        // Restore & force delete for soft‑deleted records
        Route::patch('employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
        Route::delete('employees/{id}/force', [EmployeeController::class, 'forceDelete'])->name('employees.force-delete');
        Route::patch('departments/{id}/restore', [DepartmentController::class, 'restore'])->name('departments.restore');
        Route::delete('departments/{id}/force', [DepartmentController::class, 'forceDelete'])->name('departments.force-delete');

        // Audit Logs
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

require __DIR__.'/auth.php';