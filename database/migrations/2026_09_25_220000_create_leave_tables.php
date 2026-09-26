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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->unsignedInteger('days_per_year')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('carry_forward')->default(false);
            $table->unsignedInteger('max_carry')->default(0);
            $table->string('status', 20)->default('active'); // active, inactive
            $table->string('color', 20)->default('#4f46e5');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('allocated', 5, 1)->default(0);
            $table->decimal('used', 5, 1)->default(0);
            $table->decimal('carried', 5, 1)->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year']);
            $table->index(['employee_id', 'year']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('days', 5, 1)->default(1.0);
            $table->boolean('half_day')->default(false);
            $table->string('half_day_type', 20)->nullable(); // first_half, second_half
            $table->text('reason');
            $table->string('attachment')->nullable();
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, cancelled
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remark')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['from_date', 'to_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_types');
    }
};
