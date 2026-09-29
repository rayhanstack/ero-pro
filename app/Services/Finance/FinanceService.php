<?php

namespace App\Services\Finance;

use App\Enums\StatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Helpers\MediaHelper;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Get paginated transactions for Income or Expense.
     */
    public function getPaginatedTransactions(string $type, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildTransactionQuery($type, $filters);

        return $query->latest('date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Calculate summary statistics and totals based on active filters.
     */
    public function getTransactionStats(string $type, array $filters = []): array
    {
        $filteredTotal = (float) $this->buildTransactionQuery($type, $filters)->sum('amount');
        $thisMonthTotal = (float) Transaction::where('type', $type)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        $todayTotal = (float) Transaction::where('type', $type)
            ->whereDate('date', today())
            ->sum('amount');
        $allTimeTotal = (float) Transaction::where('type', $type)->sum('amount');

        return [
            'filtered_total' => round($filteredTotal, 2),
            'this_month' => round($thisMonthTotal, 2),
            'today' => round($todayTotal, 2),
            'all_time' => round($allTimeTotal, 2),
        ];
    }

    /**
     * Build base query for transactions.
     */
    protected function buildTransactionQuery(string $type, array $filters = []): Builder
    {
        $query = Transaction::query()
            ->where('type', $type)
            ->with([
                'account',
                'toAccount',
                'incomeCategory',
                'expenseCategory',
                'client',
                'project',
                'employee',
                'invoice',
                'creator',
            ]);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['start_date']) || ! empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'] ?? null, $filters['end_date'] ?? null);
        }

        return $query;
    }

    /**
     * Create a new income or expense transaction.
     */
    public function createTransaction(array $data, ?UploadedFile $attachment = null): Transaction
    {
        return DB::transaction(function () use ($data, $attachment) {
            $attachmentPath = null;
            if ($attachment) {
                $upload = MediaHelper::upload($attachment, 'finance/transactions');
                $attachmentPath = $upload['file'] ?? null;
            }

            $count = Transaction::whereDate('created_at', today())->count() + 1;
            $trxNumber = sprintf('TRX-%s-%04d', now()->format('Ym'), $count);

            $type = $data['type'] instanceof TransactionTypeEnum ? $data['type']->value : $data['type'];
            $categoryType = $type === 'income' ? 'income_category' : 'expense_category';

            return Transaction::create([
                'transaction_number' => $trxNumber,
                'type' => $type,
                'account_id' => $data['account_id'],
                'to_account_id' => $data['to_account_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'category_type' => $categoryType,
                'amount' => (float) $data['amount'],
                'date' => $data['date'],
                'reference' => $data['reference'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'client_id' => $data['client_id'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'employee_id' => $data['employee_id'] ?? null,
                'attachment' => $attachmentPath,
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Update an existing transaction.
     */
    public function updateTransaction(Transaction $transaction, array $data, ?UploadedFile $attachment = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $data, $attachment) {
            if ($attachment) {
                $upload = MediaHelper::upload($attachment, 'finance/transactions');
                $data['attachment'] = $upload['file'] ?? null;
            }

            $type = $data['type'] instanceof TransactionTypeEnum ? $data['type']->value : ($data['type'] ?? $transaction->type->value);
            $data['category_type'] = $type === 'income' ? 'income_category' : 'expense_category';

            $transaction->update($data);

            return $transaction->fresh();
        });
    }

    /**
     * Delete a transaction.
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            return (bool) $transaction->delete();
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                            Categories Management                           */
    /* -------------------------------------------------------------------------- */

    public function getCategories(string $type = 'income'): \Illuminate\Database\Eloquent\Collection
    {
        return $type === 'income'
            ? IncomeCategory::orderBy('name')->get()
            : ExpenseCategory::orderBy('name')->get();
    }

    public function createCategory(array $data): IncomeCategory|ExpenseCategory
    {
        $type = $data['type'] ?? 'income';
        unset($data['type']);
        $data['status'] = $data['status'] ?? StatusEnum::ACTIVE;

        return $type === 'income'
            ? IncomeCategory::create($data)
            : ExpenseCategory::create($data);
    }

    public function updateCategory(int $id, string $type, array $data): IncomeCategory|ExpenseCategory
    {
        unset($data['type']);
        $category = $type === 'income' ? IncomeCategory::findOrFail($id) : ExpenseCategory::findOrFail($id);
        $category->update($data);

        return $category;
    }

    public function deleteCategory(int $id, string $type): bool
    {
        $category = $type === 'income' ? IncomeCategory::findOrFail($id) : ExpenseCategory::findOrFail($id);

        return (bool) $category->delete();
    }
}
