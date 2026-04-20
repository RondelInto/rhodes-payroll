<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\CompanySetting;
use App\Models\CustomDeduction;
use App\Models\CustomAllowance;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create users (idempotent)
        User::updateOrCreate(
            ['email' => 'admin@rhodes.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@rhodes.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'role' => 'user'
            ]
        );

        // Create departments (idempotent)
        $departments = [
            ['code' => 'HR', 'name' => 'Human Resources'],
            ['code' => 'FIN', 'name' => 'Finance'],
            ['code' => 'IT', 'name' => 'IT Department'],
            ['code' => 'OPS', 'name' => 'Operations'],
            ['code' => 'SM', 'name' => 'Sales & Marketing'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        // Create employees (only if they don't exist by email)
        $employees = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan.delacruz@rhodes.com',
                'phone' => '09171234567',
                'position' => 'HR Manager',
                'basic_salary' => 80000,
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'maria.santos@rhodes.com',
                'phone' => '09171234568',
                'position' => 'Accountant',
                'basic_salary' => 45000,
            ],
            [
                'first_name' => 'Jose',
                'last_name' => 'Rizal',
                'email' => 'jose.rizal@rhodes.com',
                'phone' => '09171234569',
                'position' => 'Software Developer',
                'basic_salary' => 55000,
            ],
        ];

        foreach ($employees as $empData) {
            Employee::firstOrCreate(
                ['email' => $empData['email']],
                array_merge($empData, [
                    'employee_id' => 'EMP-' . str_pad(rand(1, 999), 6, '0', STR_PAD_LEFT),
                    'date_of_birth' => '1990-01-01',
                    'gender' => 'male',
                    'address' => '123 Main St',
                    'city' => 'Makati',
                    'province' => 'Metro Manila',
                    'zip_code' => '1200',
                    'hire_date' => '2023-01-01',
                    'department_id' => Department::inRandomOrder()->first()->id,
                    'employment_type' => 'regular',
                    'status' => 'active',
                    'sss_number' => '12-3456789-0',
                    'philhealth_number' => '123456789012',
                    'pagibig_number' => '1234-5678-9012',
                    'tin_number' => '123-456-789-000'
                ])
            );
        }

        // Create payroll period (idempotent)
        PayrollPeriod::firstOrCreate(
            ['name' => 'January 2024 - First Half'],
            [
                'period_type' => 'semi-monthly',
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-15',
                'pay_date' => '2024-01-20',
                'status' => 'paid'
            ]
        );

        // Create company settings (idempotent)
        $settings = [
            ['key' => 'company_name', 'value' => 'Rhodes Corporation', 'type' => 'text'],
            ['key' => 'company_address', 'value' => 'Makati City, Philippines', 'type' => 'text'],
            ['key' => 'company_tin', 'value' => '123-456-789-000', 'type' => 'text'],
            ['key' => 'working_hours_per_day', 'value' => '8', 'type' => 'number'],
            ['key' => 'overtime_rate_multiplier', 'value' => '1.25', 'type' => 'decimal'],
            ['key' => 'late_deduction_per_hour', 'value' => '50', 'type' => 'decimal'],
            ['key' => 'night_differential_rate', 'value' => '1.10', 'type' => 'decimal'],
            ['key' => 'holiday_rate_multiplier', 'value' => '2.0', 'type' => 'decimal'],
        ];

        foreach ($settings as $setting) {
            CompanySetting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        // Create custom deductions (idempotent)
        CustomDeduction::firstOrCreate(
            ['name' => 'Cash Advance'],
            ['type' => 'fixed', 'amount' => 5000, 'is_active' => true]
        );
        CustomDeduction::firstOrCreate(
            ['name' => 'Uniform'],
            ['type' => 'fixed', 'amount' => 500, 'is_active' => true]
        );

        // Create custom allowances (idempotent)
        CustomAllowance::firstOrCreate(
            ['name' => 'Transportation'],
            ['type' => 'fixed', 'amount' => 2000, 'is_active' => true]
        );
        CustomAllowance::firstOrCreate(
            ['name' => 'Meal Allowance'],
            ['type' => 'fixed', 'amount' => 1500, 'is_active' => true]
        );
    }
}