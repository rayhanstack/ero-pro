<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Enums\TransactionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreTransactionRequest;
use App\Http\Requests\Finance\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Client;
use App\Models\IncomeCategory;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Finance\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeController extends Controller
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Display a listing of income transactions.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'account_id', 'category_id', 'client_id', 'project_id', 'employee_id', 'start_date', 'end_date']);
        $incomes = $this->financeService->getPaginatedTransactions('income', $filters, 15);
        $stats = $this->financeService->getTransactionStats('income', $filters);

        $accounts = Account::active()->orderBy('name')->get();
        $categories = IncomeCategory::active()->orderBy('name')->get();
        $clients = Client::active()->orderBy('company_name')->get();
        $projects = Project::orderBy('title')->get();
        $employees = User::active()->whereHas('employeeDetail')->orderBy('name')->get();

        $title = _trans('common.Income Transactions');

        return view('admin.finance.income.index', compact(
            'title',
            'incomes',
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
     * Store a newly created income transaction.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['type'] = TransactionTypeEnum::INCOME;

        $transaction = $this->financeService->createTransaction(
            $data,
            $request->file('attachment')
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Income recorded successfully.'),
                'data' => $transaction,
            ]);
        }

        return redirect()->route('finance.income.index')
            ->with('success', _trans('common.Income recorded successfully.'));
    }

    /**
     * Update an income transaction.
     */
    public function update(UpdateTransactionRequest $request, Transaction $income): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['type'] = TransactionTypeEnum::INCOME;

        $updated = $this->financeService->updateTransaction(
            $income,
            $data,
            $request->file('attachment')
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Income updated successfully.'),
                'data' => $updated,
            ]);
        }

        return redirect()->route('finance.income.index')
            ->with('success', _trans('common.Income updated successfully.'));
    }

    /**
     * Remove an income transaction.
     */
    public function destroy(Request $request, Transaction $income): RedirectResponse|JsonResponse
    {
        $this->financeService->deleteTransaction($income);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Income deleted successfully.'),
            ]);
        }

        return redirect()->route('finance.income.index')
            ->with('success', _trans('common.Income deleted successfully.'));
    }
}
