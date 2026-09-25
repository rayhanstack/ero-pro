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
        // Shifts table
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('grace_minutes')->default(0);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Weekends table
        Schema::create('weekends', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week')->unique(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $table->string('name', 20); // Sunday, Monday, etc.
            $table->boolean('is_weekend')->default(false)->index();
            $table->timestamps();
        });

        // Holidays table
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('from_date')->index();
            $table->date('to_date')->index();
            $table->string('type', 30)->default('public')->index(); // public, company
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('weekends');
        Schema::dropIfExists('shifts');
    }
};
