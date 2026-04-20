<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->text('address');
            $table->string('city');
            $table->string('province');
            $table->string('zip_code');
            $table->date('hire_date');
            $table->foreignId('department_id')->constrained()->onDelete('restrict');
            $table->string('position');
            $table->enum('employment_type', ['regular', 'contractual', 'probationary', 'project-based']);
            $table->decimal('basic_salary', 12, 2);
            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('tin_number')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index('employee_id');
            $table->index('department_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};