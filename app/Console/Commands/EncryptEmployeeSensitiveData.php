<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class EncryptEmployeeSensitiveData extends Command
{
    protected $signature = 'employee:encrypt';
    protected $description = 'Encrypt existing sensitive employee fields (salary, govt numbers)';

    public function handle()
    {
        $employees = Employee::withoutEvents(function () {
            return Employee::all();
        });

        if ($employees->isEmpty()) {
            $this->info('No employees found.');
            return;
        }

        $bar = $this->output->createProgressBar(count($employees));
        $bar->start();

        foreach ($employees as $employee) {
            // Force re‑save using the mutators (which will encrypt)
            $employee->basic_salary = $employee->getOriginal('basic_salary');
            $employee->sss_number = $employee->getOriginal('sss_number');
            $employee->philhealth_number = $employee->getOriginal('philhealth_number');
            $employee->pagibig_number = $employee->getOriginal('pagibig_number');
            $employee->tin_number = $employee->getOriginal('tin_number');
            $employee->saveQuietly(); // avoid event loops
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All existing employee sensitive data has been encrypted.');
    }
}