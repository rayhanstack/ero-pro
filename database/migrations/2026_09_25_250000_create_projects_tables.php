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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 200);
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();
            $table->decimal('budget', 15, 2)->default(0.00);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->string('priority', 20)->default('medium'); // low, medium, high, urgent
            $table->string('status', 20)->default('planning'); // planning, active, on_hold, completed, cancelled
            $table->unsignedTinyInteger('progress')->default(0); // 0 - 100%
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['name', 'status']);
            $table->index(['client_id', 'status']);
            $table->index('priority');
            $table->index('manager_id');
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 50)->default('member');
            $table->timestamps();

            $table->unique(['project_id', 'employee_id']);
            $table->index('project_id');
            $table->index('employee_id');
        });

        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('cost', 15, 2)->nullable()->default(0.00);
            $table->string('status', 20)->default('incomplete'); // incomplete, complete
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });

        Schema::create('project_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->string('file_size', 50)->nullable();
            $table->string('file_type', 50)->nullable();
            $table->timestamps();

            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_files');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};
