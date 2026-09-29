<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\AccountTypeEnum;
use App\Enums\ClientStatusEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\PayrollPeriodStatusEnum;
use App\Enums\PayslipStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Events\PayslipPaid;
use App\Models\Account;
use App\Models\Client;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\Invoice;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin@erp.test',
            'status' => EmployeeStatusEnum::ACTIVE,
        ]);
        $this->admin->assignRole('Super Admin');
    }

    public function test_user_can_view_accounts_and_live_balances_calculate_correctly(): void
    {
        $this->actingAs($this->admin);

        $account = Account::create([
            'name' => 'Operational Bank',
            'account_number' => '123456789',
            'type' => AccountTypeEnum::BANK,
            'opening_balance' => 10000.00,
            'status' => AccountStatusEnum::ACTIVE,
        ]);

        $incCategory = IncomeCategory::create(['name' => 'Consulting']);
        $expCategory = ExpenseCategory::create(['name' => 'Office Rent']);

        // Record Income of 5000
        Transaction::create([
            'type' => TransactionTypeEnum::INCOME,
            'account_id' => $account->id,
            'category_id' => $incCategory->id,
            'amount' => 5000.00,
            'date' => now()->toDateString(),
            'payment_method' => 'Bank Transfer',
        ]);

        // Record Expense of 2000
        Transaction::create([
            'type' => TransactionTypeEnum::EXPENSE,
            'account_id' => $account->id,
            'category_id' => $expCategory->id,
            'amount' => 2000.00,
            'date' => now()->toDateString(),
            'payment_method' => 'Bank Transfer',
        ]);

        // Live balance should be 10000 + 5000 - 2000 = 13000
        $this->assertEquals(13000.00, (float)$account->fresh()->balance);

        $response = $this->get(route('finance.accounts.index'));
        $response->assertOk();
        $response->assertSee('Operational Bank');
        $response->assertSee('13,000');
    }

    public function test_inter_account_transfers_update_balances_of_source_and_destination_accounts(): void
    {
        $this->actingAs($this->admin);

        $sourceAcc = Account::create([
            'name' => 'Main Bank',
            'type' => AccountTypeEnum::BANK,
            'opening_balance' => 20000.00,
            'status' => AccountStatusEnum::ACTIVE,
        ]);

        $destAcc = Account::create([
            'name' => 'Petty Cash',
            'type' => AccountTypeEnum::CASH,
            'opening_balance' => 1000.00,
            'status' => AccountStatusEnum::ACTIVE,
        ]);

        $response = $this->post(route('finance.accounts.transfer'), [
            'from_account_id' => $sourceAcc->id,
            'to_account_id' => $destAcc->id,
            'amount' => 4000.00,
            'date' => now()->toDateString(),
            'reference' => 'TRF-001',
            'note' => 'Replenish Petty Cash',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(16000.00, (float)$sourceAcc->fresh()->balance);
        $this->assertEquals(5000.00, (float)$destAcc->fresh()->balance);

        // Verify transaction record created
        $this->assertDatabaseHas('transactions', [
            'type' => TransactionTypeEnum::TRANSFER->value,
            'account_id' => $sourceAcc->id,
            'to_account_id' => $destAcc->id,
            'amount' => 4000.00,
        ]);
    }

    public function test_inter_account_transfer_fails_if_source_account_has_insufficient_balance(): void
    {
        $this->actingAs($this->admin);

        $sourceAcc = Account::create([
            'name' => 'Empty Bank',
            'type' => AccountTypeEnum::BANK,
            'opening_balance' => 500.00,
            'status' => AccountStatusEnum::ACTIVE,
        ]);

        $destAcc = Account::create([
            'name' => 'Petty Cash',
            'type' => AccountTypeEnum::CASH,
            'opening_balance' => 1000.00,
            'status' => AccountStatusEnum::ACTIVE,
        ]);

        $response = $this->post(route('finance.accounts.transfer'), [
            'from_account_id' => $sourceAcc->id,
            'to_account_id' => $destAcc->id,
            'amount' => 5000.00,
            'date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertEquals(500.00, (float)$sourceAcc->fresh()->balance);
    }

    public function test_income_and_expense_crud_operations_work_properly(): void
    {
        $this->actingAs($this->admin);

        $account = Account::create([
            'name' => 'Savings Account',
            'type' => AccountTypeEnum::BANK,
            'opening_balance' => 1000.00,
            'status' => AccountStatusEnum::ACTIVE,
        ]);

        $incomeCat = IncomeCategory::create(['name' => 'Product Sales']);
        $expenseCat = ExpenseCategory::create(['name' => 'Server Subscriptions']);

        // Create Income
        $incomeResp = $this->post(route('finance.income.store'), [
            'account_id' => $account->id,
            'category_id' => $incomeCat->id,
            'amount' => 7500.00,
            'date' => now()->toDateString(),
            'payment_method' => 'Bank Transfer',
            'reference' => 'INC-999',
            'note' => 'Quarterly license revenue',
        ]);
        $incomeResp->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'type' => TransactionTypeEnum::INCOME->value,
            'amount' => 7500.00,
            'reference' => 'INC-999',
        ]);

        // Create Expense
        $expenseResp = $this->post(route('finance.expense.store'), [
            'account_id' => $account->id,
            'category_id' => $expenseCat->id,
            'amount' => 1200.00,
            'date' => now()->toDateString(),
            'payment_method' => 'Credit Card',
            'reference' => 'EXP-888',
            'note' => 'AWS Cloud hosting',
        ]);
        $expenseResp->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'type' => TransactionTypeEnum::EXPENSE->value,
            'amount' => 1200.00,
            'reference' => 'EXP-888',
        ]);

        // Check account balance: 1000 + 7500 - 1200 = 7300
        $this->assertEquals(7300.00, (float)$account->fresh()->balance);
    }

    public function test_payslip_paid_event_automatically_creates_an_expense_transaction(): void
    {
        $account = Account::create([
            'name' => 'Corporate Main Bank',
            'type' => AccountTypeEnum::BANK,
            'opening_balance' => 100000.00,
            'status' => AccountStatusEnum::ACTIVE,
        ]);

        $employee = User::factory()->create([
            'name' => 'John Doe Engineer',
            'status' => EmployeeStatusEnum::ACTIVE,
        ]);

        $period = PayrollPeriod::create([
            'month' => 9,
            'year' => 2026,
            'status' => PayrollPeriodStatusEnum::PROCESSED,
            'total_basic' => 50000.00,
            'total_earnings' => 55000.00,
            'total_deductions' => 2000.00,
            'total_net_pay' => 53000.00,
            'total_employees' => 1,
        ]);

        $payslip = Payslip::create([
            'payroll_period_id' => $period->id,
            'employee_id' => $employee->id,
            'basic' => 50000.00,
            'total_earnings' => 55000.00,
            'total_deductions' => 2000.00,
            'overtime_amount' => 0.00,
            'absent_deduction' => 0.00,
            'tax' => 0.00,
            'bonus' => 5000.00,
            'net_pay' => 53000.00,
            'working_days' => 22,
            'present_days' => 22,
            'leave_days' => 0,
            'absent_days' => 0,
            'status' => PayslipStatusEnum::APPROVED,
        ]);

        // Dispatch PayslipPaid event
        event(new PayslipPaid($payslip));

        // Assert that an Expense transaction is created
        $this->assertDatabaseHas('transactions', [
            'type' => TransactionTypeEnum::EXPENSE->value,
            'employee_id' => $employee->id,
            'payslip_id' => $payslip->id,
            'amount' => 53000.00,
        ]);

        // Account balance should now reflect the deduction
        $this->assertEquals(47000.00, (float)$account->fresh()->balance);
    }

    public function test_user_can_create_invoice_with_multiple_line_items_tax_and_discount(): void
    {
        $this->actingAs($this->admin);

        $client = Client::create([
            'code' => Client::generateCode(),
            'company_name' => 'Acme Corporation',
            'contact_name' => 'John Doe',
            'email' => 'client@acme.test',
            'status' => ClientStatusEnum::ACTIVE,
        ]);

        $project = Project::create([
            'code' => Project::generateCode(),
            'client_id' => $client->id,
            'name' => 'Enterprise E-Commerce',
            'status' => ProjectStatusEnum::ACTIVE,
        ]);

        $invoiceData = [
            'client_id' => $client->id,
            'project_id' => $project->id,
            'invoice_number' => 'INV-TEST-001',
            'issue_date' => '2026-09-29',
            'due_date' => '2026-10-15',
            'discount' => 500.00,
            'tax' => 250.00,
            'status' => InvoiceStatusEnum::SENT->value,
            'notes' => 'Thank you for your business',
            'terms' => 'Net 15',
            'items' => [
                [
                    'item_name' => 'Backend API Development',
                    'description' => 'REST APIs with auth & payment integration',
                    'quantity' => 10,
                    'unit_price' => 500.00, // Subtotal: 5000
                ],
                [
                    'item_name' => 'Frontend Dashboard Design',
                    'description' => 'Bootstrap 5 responsive UI',
                    'quantity' => 1,
                    'unit_price' => 2000.00, // Subtotal: 2000
                ],
            ],
        ];

        $response = $this->post(route('finance.invoices.store'), $invoiceData);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $invoice = Invoice::where('invoice_number', 'INV-TEST-001')->first();
        $this->assertNotNull($invoice);
        // Subtotal: 7000, Discount: 500, Tax: 250 => Total: 6750, Due: 6750
        $this->assertEquals(7000.00, (float)$invoice->subtotal);
        $this->assertEquals(500.00, (float)$invoice->discount);
        $this->assertEquals(250.00, (float)$invoice->tax);
        $this->assertEquals(6750.00, (float)$invoice->total_amount);
        $this->assertEquals(6750.00, (float)$invoice->due_amount);
        $this->assertCount(2, $invoice->items);
    }

    public function test_recording_invoice_payment_creates_income_transaction_and_updates_invoice_status(): void
    {
        $this->actingAs($this->admin);

        $account = Account::create([
            'name' => 'Corporate Bank',
            'type' => AccountTypeEnum::BANK,
            'opening_balance' => 5000.00,
            'status' => AccountStatusEnum::ACTIVE,
        ]);

        $client = Client::create([
            'code' => Client::generateCode(),
            'company_name' => 'Apex Solutions',
            'contact_name' => 'Jane Smith',
            'email' => 'apex@solutions.test',
            'status' => ClientStatusEnum::ACTIVE,
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-2026-PAY',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 10000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total_amount' => 10000.00,
            'paid_amount' => 0.00,
            'due_amount' => 10000.00,
            'status' => InvoiceStatusEnum::SENT,
        ]);

        // 1. Record Partial Payment of 4000
        $payment1 = $this->post(route('finance.invoices.payments.store', $invoice->id), [
            'account_id' => $account->id,
            'amount' => 4000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Bank Transfer',
            'reference' => 'PAY-REC-001',
        ]);
        $payment1->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(4000.00, (float)$invoice->paid_amount);
        $this->assertEquals(6000.00, (float)$invoice->due_amount);
        $this->assertEquals(InvoiceStatusEnum::PARTIAL, $invoice->status);
        $this->assertEquals(9000.00, (float)$account->fresh()->balance);

        // 2. Record Final Payment of 6000
        $payment2 = $this->post(route('finance.invoices.payments.store', $invoice->id), [
            'account_id' => $account->id,
            'amount' => 6000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Bank Transfer',
            'reference' => 'PAY-REC-002',
        ]);
        $payment2->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(10000.00, (float)$invoice->paid_amount);
        $this->assertEquals(0.00, (float)$invoice->due_amount);
        $this->assertEquals(InvoiceStatusEnum::PAID, $invoice->status);
        $this->assertEquals(15000.00, (float)$account->fresh()->balance);

        // Verify 2 Income transactions linked to this invoice
        $this->assertEquals(2, Transaction::where('invoice_id', $invoice->id)->count());
    }

    public function test_invoice_pdf_download_returns_valid_pdf_response(): void
    {
        $this->actingAs($this->admin);

        $client = Client::create([
            'code' => Client::generateCode(),
            'company_name' => 'Apex Solutions',
            'contact_name' => 'Jane Smith',
            'email' => 'apex@solutions.test',
            'status' => ClientStatusEnum::ACTIVE,
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-PDF-001',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => 3000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total_amount' => 3000.00,
            'paid_amount' => 0.00,
            'due_amount' => 3000.00,
            'status' => InvoiceStatusEnum::SENT,
        ]);

        $invoice->items()->create([
            'item_name' => 'Consulting Services',
            'quantity' => 1,
            'unit_price' => 3000.00,
            'total_price' => 3000.00,
        ]);

        $response = $this->get(route('finance.invoices.download-pdf', $invoice->id));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_client_show_page_displays_linked_invoices_and_computed_financial_totals(): void
    {
        $this->actingAs($this->admin);

        $client = Client::create([
            'code' => Client::generateCode(),
            'company_name' => 'Globex Corporation',
            'contact_name' => 'Hank Scorpio',
            'email' => 'scorpio@globex.test',
            'status' => ClientStatusEnum::ACTIVE,
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-GLOBEX-101',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => 15000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total_amount' => 15000.00,
            'paid_amount' => 10000.00,
            'due_amount' => 5000.00,
            'status' => InvoiceStatusEnum::PARTIAL,
        ]);

        $response = $this->get(route('clients.show', $client->id));
        $response->assertOk();
        $response->assertSee('INV-GLOBEX-101');
        $response->assertSee('10,000');
        $response->assertSee('15,000');
    }

    public function test_validation_errors_when_creating_account_and_invoice_with_invalid_data(): void
    {
        $this->actingAs($this->admin);

        // Account validation
        $accResponse = $this->post(route('finance.accounts.store'), [
            'name' => '',
            'type' => 'invalid-type',
            'opening_balance' => -100,
        ]);
        $accResponse->assertSessionHasErrors(['name', 'type', 'opening_balance']);

        // Invoice validation
        $invResponse = $this->post(route('finance.invoices.store'), [
            'client_id' => 99999,
            'items' => [],
        ]);
        $invResponse->assertSessionHasErrors(['client_id', 'items']);
    }

    public function test_unauthorized_user_cannot_access_finance_module(): void
    {
        $regularUser = User::factory()->create([
            'email' => 'staff@erp.test',
            'status' => EmployeeStatusEnum::ACTIVE,
        ]);

        $this->actingAs($regularUser);

        $this->get(route('finance.accounts.index'))->assertForbidden();
        $this->get(route('finance.income.index'))->assertForbidden();
        $this->get(route('finance.expense.index'))->assertForbidden();
        $this->get(route('finance.categories.index'))->assertForbidden();
        $this->get(route('finance.invoices.index'))->assertForbidden();
    }

    public function test_sidebar_active_state_for_finance(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('finance.accounts.index'));
        $response->assertOk();
        $response->assertSee('financeMenu');
        $response->assertSee(route('finance.accounts.index'));
    }
}



