<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payroll_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained('payroll_periods')->onDelete('cascade');
            $table->decimal('basic_pay', 12, 2);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('holiday_pay', 12, 2)->default(0);
            $table->json('allowances')->nullable();
            $table->decimal('gross_pay', 12, 2);
            $table->decimal('sss_contribution', 12, 2);
            $table->decimal('philhealth_contribution', 12, 2);
            $table->decimal('pagibig_contribution', 12, 2);
            $table->decimal('withholding_tax', 12, 2);
            $table->json('other_deductions')->nullable();
            $table->decimal('total_deductions', 12, 2);
            $table->decimal('net_pay', 12, 2);
            $table->date('processed_date');
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamps();
            
            $table->unique(['employee_id', 'period_id']);
            $table->index('period_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_transactions');
    }
};
