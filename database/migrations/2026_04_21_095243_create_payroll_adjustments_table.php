<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('payroll_adjustments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained()->onDelete('cascade');
        $table->foreignId('period_id')->constrained('payroll_periods')->onDelete('cascade');
        $table->enum('type', ['bonus', 'deduction']);
        $table->decimal('amount', 12, 2);
        $table->string('description')->nullable();
        $table->timestamps();

        $table->unique(['employee_id', 'period_id', 'type', 'description'], 'payroll_adj_unique');
    });
}

    public function down()
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};