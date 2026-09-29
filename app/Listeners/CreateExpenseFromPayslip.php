<?php

namespace App\Listeners;

use App\Enums\AccountStatusEnum;
use App\Enums\StatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Events\PayslipPaid;
use App\Models\Account;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class CreateExpenseFromPayslip
{
    /**
     * Handle the PayslipPaid event.
     */
    public function handle(PayslipPaid $event): void
    {
        $payslip = $event->payslip;

        // Prevent duplicate expense transaction for the same payslip
        if (Transaction::where('payslip_id', $payslip->id)->exists()) {
            return;
        }

        // Find or create default payroll expense category
        $category = ExpenseCategory::firstOrCreate(
            ['name' => 'Salary & Payroll'],
            [
                'status' => StatusEnum::ACTIVE,
                'color' => '#6366f1',
                'icon' => 'bi-wallet2',
                'description' => 'Employee salaries and payroll disbursements',
            ]
        );

        // Find an active bank or cash account
        $account = Account::active()->first();
        if (! $account) {
            $account = Account::firstOrCreate(
                ['name' => 'Main Operating Account'],
                [
                    'type' => 'bank',
                    'opening_balance' => 0.00,
                    'status' => AccountStatusEnum::ACTIVE,
                ]
            );
        }

        $payslip->loadMissing(['employee', 'payrollPeriod']);

        $trxCount = Transaction::whereDate('created_at', today())->count() + 1;
        $trxNumber = sprintf('TRX-%s-%04d', now()->format('Ym'), $trxCount);

        Transaction::create([
            'transaction_number' => $trxNumber,
            'type' => TransactionTypeEnum::EXPENSE,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'category_type' => 'expense_category',
            'amount' => $payslip->net_pay,
            'date' => $payslip->paid_at ? $payslip->paid_at->format('Y-m-d') : now()->format('Y-m-d'),
            'reference' => "Payslip #{$payslip->payslip_number}",
            'payment_method' => $payslip->payment_method ?? 'Bank Transfer',
            'employee_id' => $payslip->employee_id,
            'payslip_id' => $payslip->id,
            'note' => sprintf(
                'Salary disbursement for %s (%s)',
                $payslip->employee?->name ?? 'Employee',
                $payslip->payrollPeriod?->formatted_period ?? 'Period'
            ),
        ]);
    }
}
