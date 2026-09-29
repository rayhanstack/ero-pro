<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Enums\TransactionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreTransactionRequest;
use App\Http\Requests\Finance\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Client;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Finance\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Display a listing of expense transactions.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'account_id', 'category_id', 'client_id', 'project_id', 'employee_id', 'start_date', 'end_date']);
        $expenses = $this->financeService->getPaginatedTransactions('expense', $filters, 15);
        $stats = $this->financeService->getTransactionStats('expense', $filters);

        $accounts = Account::active()->orderBy('name')->get();
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $clients = Client::active()->orderBy('company_name')->get();
        $projects = Project::orderBy('title')->get();
        $employees = User::active()->whereHas('employeeDetail')->orderBy('name')->get();

        $title = _trans('common.Expense Transactions');

        return view('admin.finance.expense.index', compact(
            'title',
            'expenses',
            'stats',
            'filters',
            'accounts',
            'categories',
            'clients',
            'projects',
            'employees'
        ));
    }

    /**
     * Store a newly created expense transaction.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['type'] = TransactionTypeEnum::EXPENSE;

        $transaction = $this->financeService->createTransaction(
            $data,
            $request->file('attachment')
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Expense recorded successfully.'),
                'data' => $transaction,
            ]);
        }

        return redirect()->route('finance.expense.index')
            ->with('success', _trans('common.Expense recorded successfully.'));
    }

    /**
     * Update an expense transaction.
     */
    public function update(UpdateTransactionRequest $request, Transaction $expense): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['type'] = TransactionTypeEnum::EXPENSE;

        $updated = $this->financeService->updateTransaction(
            $expense,
            $data,
            $request->file('attachment')
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Expense updated successfully.'),
                'data' => $updated,
            ]);
        }

        return redirect()->route('finance.expense.index')
            ->with('success', _trans('common.Expense updated successfully.'));
    }

    /**
     * Remove an expense transaction.
     */
    public function destroy(Request $request, Transaction $expense): RedirectResponse|JsonResponse
    {
        $this->financeService->deleteTransaction($expense);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Expense deleted successfully.'),
            ]);
        }

        return redirect()->route('finance.expense.index')
            ->with('success', _trans('common.Expense deleted successfully.'));
    }
}
