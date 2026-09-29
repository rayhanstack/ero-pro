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
        // 1. Accounts
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20)->default('bank'); // bank, cash, mobile
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->string('status', 20)->default('active'); // active, inactive
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
        });

        // 2. Income Categories
        Schema::create('income_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status', 20)->default('active'); // active, inactive
            $table->string('color', 20)->nullable()->default('#10b981');
            $table->string('icon', 50)->nullable()->default('bi-graph-up-arrow');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        // 3. Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status', 20)->default('active'); // active, inactive
            $table->string('color', 20)->nullable()->default('#ef4444');
            $table->string('icon', 50)->nullable()->default('bi-graph-down-arrow');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        // 4. Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->string('discount_type', 20)->default('fixed'); // fixed, percent
            $table->decimal('discount_value', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('due_amount', 15, 2)->default(0.00);
            $table->string('status', 20)->default('draft'); // draft, sent, partial, paid, overdue, cancelled
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
            $table->index('project_id');
            $table->index('status');
            $table->index('issue_date');
            $table->index('due_date');
        });

        // 5. Invoice Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index('invoice_id');
        });

        // 6. Transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->string('type', 20); // income, expense, transfer
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('category_type', 30)->nullable(); // income_category, expense_category
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->date('date');
            $table->string('reference')->nullable();
            $table->string('payment_method', 50)->nullable()->default('Cash');
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('payslip_id')->nullable()->constrained('payslips')->nullOnDelete();
            $table->text('attachment')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('account_id');
            $table->index('to_account_id');
            $table->index('date');
            $table->index('client_id');
            $table->index('project_id');
            $table->index('employee_id');
            $table->index('invoice_id');
        });

        // 7. Invoice Payments
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('payment_method', 50)->default('Bank Transfer');
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('invoice_id');
            $table->index('account_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('income_categories');
        Schema::dropIfExists('accounts');
    }
};
