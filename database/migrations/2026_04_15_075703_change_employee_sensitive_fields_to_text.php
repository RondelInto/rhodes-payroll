<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Change decimal fields to text to store encrypted strings
            $table->text('basic_salary')->change();
            // The following fields are already strings, but ensure they are long enough
            // If they are varchar(255), they are fine. Optionally change to text:
            $table->text('sss_number')->nullable()->change();
            $table->text('philhealth_number')->nullable()->change();
            $table->text('pagibig_number')->nullable()->change();
            $table->text('tin_number')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('basic_salary', 12, 2)->change();
            $table->string('sss_number')->nullable()->change();
            $table->string('philhealth_number')->nullable()->change();
            $table->string('pagibig_number')->nullable()->change();
            $table->string('tin_number')->nullable()->change();
        });
    }
};