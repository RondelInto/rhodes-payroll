<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->decimal('annual_budget', 12, 2)->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('employees');
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['annual_budget', 'manager_id']);
        });
    }
};