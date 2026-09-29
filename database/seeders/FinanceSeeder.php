<?php

namespace Database\Seeders;

use App\Enums\AccountStatusEnum;
use App\Enums\AccountTypeEnum;
use App\Models\Account;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Accounts
        $accounts = [
            [
                'name' => 'Corporate Bank Account',
                'account_number' => '1029384756',
                'type' => AccountTypeEnum::BANK,
                'opening_balance' => 500000.00,
                'bank_name' => 'Eastern Bank Ltd',
                'branch' => 'Principal Branch',
                'status' => AccountStatusEnum::ACTIVE,
                'note' => 'Primary corporate operational bank account',
            ],
            [
                'name' => 'Office Petty Cash',
                'account_number' => 'CASH-001',
                'type' => AccountTypeEnum::CASH,
                'opening_balance' => 50000.00,
                'bank_name' => null,
                'branch' => null,
                'status' => AccountStatusEnum::ACTIVE,
                'note' => 'Office manager petty cash box',
            ],
            [
                'name' => 'Corporate bKash Merchant',
                'account_number' => '01700000000',
                'type' => AccountTypeEnum::MOBILE,
                'opening_balance' => 25000.00,
                'bank_name' => 'bKash Merchant',
                'branch' => null,
                'status' => AccountStatusEnum::ACTIVE,
                'note' => 'Official merchant mobile payment gateway wallet',
            ],
        ];

        foreach ($accounts as $acc) {
            Account::firstOrCreate(
                ['name' => $acc['name']],
                $acc
            );
        }

        // 2. Income Categories
        $incomeCategories = [
            ['name' => 'Project Milestones & Billing', 'description' => 'Revenue from client software development milestones'],
            ['name' => 'Consulting Services', 'description' => 'Technical and strategic IT consultancy revenue'],
            ['name' => 'Retainer & Support', 'description' => 'Monthly ongoing SLA maintenance and support retainers'],
            ['name' => 'Investment & Interest', 'description' => 'Bank interest and investment dividends'],
            ['name' => 'Other Revenue', 'description' => 'Miscellaneous business receipts'],
        ];

        foreach ($incomeCategories as $cat) {
            IncomeCategory::firstOrCreate(
                ['name' => $cat['name']],
                $cat
            );
        }

        // 3. Expense Categories
        $expenseCategories = [
            ['name' => 'Salary & Payroll', 'description' => 'Employee wages, bonuses, and compensation payouts'],
            ['name' => 'Office Rent & Utilities', 'description' => 'Monthly facility rental, electricity, water, and internet'],
            ['name' => 'Software & Cloud Infrastructure', 'description' => 'AWS, DigitalOcean, GitHub, Google Workspace subscriptions'],
            ['name' => 'Hardware & Equipment', 'description' => 'Computers, office furniture, monitors, and peripherals'],
            ['name' => 'Marketing & Advertising', 'description' => 'Digital ad campaigns, conferences, and promotional activities'],
            ['name' => 'Travel & Entertainment', 'description' => 'Client dinners, business travels, team outings, refreshments'],
            ['name' => 'General & Administrative', 'description' => 'Stationery, courier, legal, auditing, and office supplies'],
        ];

        foreach ($expenseCategories as $cat) {
            ExpenseCategory::firstOrCreate(
                ['name' => $cat['name']],
                $cat
            );
        }
    }
}
