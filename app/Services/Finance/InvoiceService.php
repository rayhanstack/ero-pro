<?php

namespace App\Services\Finance;

use App\Enums\InvoiceStatusEnum;
use App\Enums\StatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\IncomeCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    /**
     * Get paginated invoices with filters.
     */
    public function getPaginatedInvoices(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Invoice::query()
            ->with([
                'client',
                'project',
                'currency',
                'items',
                'payments.account',
            ]);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('issue_date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->latest('issue_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new invoice with line items.
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $count = Invoice::whereDate('created_at', today())->count() + 1;
            $invoiceNumber = ! empty($data['invoice_number'])
                ? $data['invoice_number']
                : sprintf('INV-%s-%04d', now()->format('Ym'), $count);

            $discountValue = $data['discount_value'] ?? ($data['discount'] ?? 0.00);
            $taxRate = $data['tax_rate'] ?? 0.00;
            $taxAmount = $data['tax_amount'] ?? ($data['tax'] ?? 0.00);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'client_id' => $data['client_id'],
                'project_id' => $data['project_id'] ?? null,
                'currency_id' => $data['currency_id'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'discount_value' => $discountValue,
                'discount_amount' => $discountValue,
                'status' => $data['status'] ?? InvoiceStatusEnum::DRAFT,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->syncInvoiceItems($invoice, $data['items'] ?? []);
            $invoice->recalculateTotals();

            return $invoice->fresh(['client', 'project', 'items']);
        });
    }

    /**
     * Update an existing invoice.
     */
    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice->update([
                'client_id' => $data['client_id'],
                'project_id' => $data['project_id'] ?? null,
                'currency_id' => $data['currency_id'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'tax_rate' => $data['tax_rate'] ?? 0.00,
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'discount_value' => $data['discount_value'] ?? 0.00,
                'status' => $data['status'] ?? $invoice->status,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);

            if (isset($data['items'])) {
                $this->syncInvoiceItems($invoice, $data['items']);
            }

            $invoice->recalculateTotals();

            return $invoice->fresh(['client', 'project', 'items']);
        });
    }

    /**
     * Delete an invoice.
     */
    public function deleteInvoice(Invoice $invoice): bool
    {
        return DB::transaction(function () use ($invoice) {
            if ($invoice->payments()->exists()) {
                throw ValidationException::withMessages([
                    'invoice' => _trans('common.Cannot delete invoice with recorded payments. Please cancel payments first.'),
                ]);
            }

            return (bool) $invoice->delete();
        });
    }

    /**
     * Record a payment against an invoice and auto-create an Income Transaction.
     */
    public function recordPayment(Invoice $invoice, array $data): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => _trans('common.Payment amount must be greater than zero.'),
                ]);
            }

            if ($amount > (float) $invoice->due_amount) {
                throw ValidationException::withMessages([
                    'amount' => _trans('common.Payment amount (:amount) cannot exceed due balance (:due).', [
                        'amount' => currency_format($amount),
                        'due' => currency_format($invoice->due_amount),
                    ]),
                ]);
            }

            $account = Account::findOrFail($data['account_id']);

            // Find or create default invoice income category
            $category = IncomeCategory::firstOrCreate(
                ['name' => 'Invoice Payment & Sales'],
                [
                    'status' => StatusEnum::ACTIVE,
                    'color' => '#10b981',
                    'icon' => 'bi-receipt-cutoff',
                    'description' => 'Client invoice settlements and revenue',
                ]
            );

            // 1. Create Income Transaction
            $trxCount = Transaction::whereDate('created_at', today())->count() + 1;
            $trxNumber = sprintf('TRX-%s-%04d', now()->format('Ym'), $trxCount);

            $transaction = Transaction::create([
                'transaction_number' => $trxNumber,
                'type' => TransactionTypeEnum::INCOME,
                'account_id' => $account->id,
                'category_id' => $category->id,
                'category_type' => 'income_category',
                'amount' => $amount,
                'date' => $data['payment_date'],
                'reference' => $data['reference'] ?? "Invoice #{$invoice->invoice_number}",
                'payment_method' => $data['payment_method'] ?? 'Bank Transfer',
                'client_id' => $invoice->client_id,
                'project_id' => $invoice->project_id,
                'invoice_id' => $invoice->id,
                'note' => $data['note'] ?? sprintf('Payment received for Invoice #%s', $invoice->invoice_number),
                'created_by' => Auth::id(),
            ]);

            // 2. Create InvoicePayment
            $payCount = InvoicePayment::whereDate('created_at', today())->count() + 1;
            $payNumber = sprintf('PAY-%s-%04d', now()->format('Ym'), $payCount);

            $payment = InvoicePayment::create([
                'payment_number' => $payNumber,
                'invoice_id' => $invoice->id,
                'transaction_id' => $transaction->id,
                'account_id' => $account->id,
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? 'Bank Transfer',
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // 3. Recalculate Invoice Balance & Status
            $invoice->recalculateTotals();

            return $payment;
        });
    }

    /**
     * Synchronize line items for an invoice.
     */
    protected function syncInvoiceItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? 0);
            $taxAmount = round((($qty * $unitPrice) * $taxRate) / 100, 2);
            $itemTotal = round(($qty * $unitPrice) + $taxAmount, 2);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_name' => $item['item_name'],
                'description' => $item['description'] ?? null,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $itemTotal,
            ]);
        }
    }

    /**
     * Get statistics summary for invoices dashboard.
     */
    public function getInvoiceStats(): array
    {
        $totalInvoiced = (float) Invoice::sum('total_amount');
        $totalReceived = (float) Invoice::sum('paid_amount');
        $totalDue = (float) Invoice::sum('due_amount');
        $totalOverdue = (float) Invoice::where('due_date', '<', today())
            ->whereIn('status', [InvoiceStatusEnum::SENT->value, InvoiceStatusEnum::PARTIAL->value, InvoiceStatusEnum::OVERDUE->value])
            ->where('due_amount', '>', 0)
            ->sum('due_amount');

        return [
            'total_invoices' => Invoice::count(),
            'total_invoiced' => round($totalInvoiced, 2),
            'total_received' => round($totalReceived, 2),
            'total_due' => round($totalDue, 2),
            'total_overdue' => round($totalOverdue, 2),
            'paid_count' => Invoice::where('status', InvoiceStatusEnum::PAID->value)->count(),
            'partial_count' => Invoice::where('status', InvoiceStatusEnum::PARTIAL->value)->count(),
            'unpaid_count' => Invoice::whereIn('status', [InvoiceStatusEnum::DRAFT->value, InvoiceStatusEnum::SENT->value])->count(),
        ];
    }

    /**
     * Generate PDF stream/download for an invoice.
     */
    public function generatePdf(Invoice $invoice): Response
    {
        $invoice->loadMissing([
            'client.country',
            'client.currency',
            'project',
            'items',
            'payments.account',
        ]);

        $company = [
            'name' => globalSetting('company_name', 'ERP Pro Inc.'),
            'email' => globalSetting('company_email', 'billing@erppro.com'),
            'phone' => globalSetting('company_phone', '+1 (555) 019-2834'),
            'address' => globalSetting('company_address', '100 Enterprise Blvd, Suite 400, Tech City'),
            'logo' => globalSetting('company_logo'),
        ];

        $pdf = Pdf::loadView('admin.finance.invoices.pdf', compact('invoice', 'company'));
        $pdf->setPaper('a4', 'portrait');

        $fileName = "Invoice-{$invoice->invoice_number}.pdf";

        return $pdf->download($fileName);
    }
}
