<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('period_id');
            $table->index('employee_id');
        });

        Schema::table('payroll_transactions', function (Blueprint $table) {
            $table->index(['period_id', 'employee_id']);
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['period_id']);
            $table->dropIndex(['employee_id']);
        });
        Schema::table('payroll_transactions', function (Blueprint $table) {
            $table->dropIndex(['period_id', 'employee_id']);
        });
    }
};