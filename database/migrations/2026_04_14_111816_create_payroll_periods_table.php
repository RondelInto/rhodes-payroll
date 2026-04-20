<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('period_type', ['weekly', 'semi-monthly', 'monthly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('pay_date');
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->timestamps();
            
            $table->index('status');
            $table->index('start_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_periods');
    }
};
