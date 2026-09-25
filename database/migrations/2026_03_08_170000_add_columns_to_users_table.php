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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('password');
            $table->string('phone')->nullable()->after('avatar');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('time_zone')->nullable()->default('UTC')->after('last_login_at');
            $table->unsignedBigInteger('employee_id')->nullable()->after('time_zone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'phone',
                'status',
                'last_login_at',
                'time_zone',
                'employee_id',
            ]);
        });
    }
};
