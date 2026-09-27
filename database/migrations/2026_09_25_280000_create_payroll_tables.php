<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Salary Components (Earnings & Deductions master)
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20)->default('earning'); // earning, deduction
            $table->string('calc_type', 30)->default('fixed'); // fixed, percent_of_basic
            $table->decimal('value', 15, 2)->default(0.00);
            $table->boolean('is_taxable')->default(true);
            $table->string('status', 20)->default('active'); // active, inactive
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
        });

        // 2. Employee Salary Components (Mapping & custom overrides)
        Schema::create('employee_salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->decimal('value', 15, 2)->nullable(); // override value, null means use component default
            $table->timestamps();

            $table->unique(['employee_id', 'component_id'], 'emp_salary_comp_unique');
            $table->index('employee_id');
            $table->index('component_id');
        });

        // 3. Payroll Periods (e.g. Sep 2026)
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // e.g. "September 2026"
            $table->unsignedTinyInteger('month'); // 1 to 12
            $table->unsignedSmallInteger('year'); // e.g. 2026
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('draft'); // draft, processed, paid, locked
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['month', 'year'], 'payroll_month_year_unique');
            $table->index('status');
        });

        // 4. Payslips
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->string('payslip_number')->unique()->nullable();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('basic', 15, 2)->default(0.00);
            $table->decimal('total_earnings', 15, 2)->default(0.00);
            $table->decimal('total_deductions', 15, 2)->default(0.00);
            $table->decimal('overtime_amount', 15, 2)->default(0.00);
            $table->decimal('absent_deduction', 15, 2)->default(0.00);
            $table->decimal('tax', 15, 2)->default(0.00);
            $table->decimal('bonus', 15, 2)->default(0.00);
            $table->decimal('net_pay', 15, 2)->default(0.00);
            $table->decimal('working_days', 8, 2)->default(0.00);
            $table->decimal('present_days', 8, 2)->default(0.00);
            $table->decimal('leave_days', 8, 2)->default(0.00);
            $table->decimal('absent_days', 8, 2)->default(0.00);
            $table->string('status', 20)->default('draft'); // draft, approved, paid
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('payroll_period_id');
            $table->index('employee_id');
            $table->index('status');
        });

        // 5. Payslip Items (Snapshot of components applied)
        Schema::create('payslip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained('payslips')->cascadeOnDelete();
            $table->foreignId('component_id')->nullable()->constrained('salary_components')->nullOnDelete();
            $table->string('name');
            $table->string('type', 20)->default('earning'); // earning, deduction
            $table->string('calc_type', 30)->nullable();
            $table->decimal('rate_or_value', 15, 2)->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index('payslip_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslip_items');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('employee_salary_components');
        Schema::dropIfExists('salary_components');
    }
};
