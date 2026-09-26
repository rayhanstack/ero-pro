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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->date('date')->index();
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->unsignedInteger('work_minutes')->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->string('status', 30)->default('present')->index(); // present, late, absent, leave, holiday, weekend, half_day
            $table->string('source', 30)->default('web'); // web, manual, import
            $table->text('note')->nullable();
            $table->string('ip', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'date']);
            $table->index(['employee_id', 'status']);
        });

        Schema::create('attendance_regularizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->date('date')->index();
            $table->dateTime('requested_in')->nullable();
            $table->dateTime('requested_out')->nullable();
            $table->text('reason');
            $table->string('status', 30)->default('pending')->index(); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_regularizations');
        Schema::dropIfExists('attendances');
    }
};
