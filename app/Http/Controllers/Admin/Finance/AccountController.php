<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Enums\AccountStatusEnum;
use App\Enums\AccountTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreAccountRequest;
use App\Http\Requests\Finance\TransferRequest;
use App\Http\Requests\Finance\UpdateAccountRequest;
use App\Models\Account;
use App\Services\Finance\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        protected AccountService $accountService
    ) {}

    /**
     * Display a listing of accounts and live balances.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'type', 'status']);
        $accounts = $this->accountService->getPaginatedAccounts($filters, 12);
        $summary = $this->accountService->getAccountsSummary();
        $types = AccountTypeEnum::cases();
        $statuses = AccountStatusEnum::cases();
        $allActiveAccounts = Account::active()->orderBy('name')->get();

        $title = _trans('common.Accounts & Live Balances');

        return view('admin.finance.accounts.index', compact(
            'title',
            'accounts',
            'summary',
            'filters',
            'types',
            'statuses',
            'allActiveAccounts'
        ));
    }

    /**
     * Store a newly created account.
     */
    public function store(StoreAccountRequest $request): RedirectResponse|JsonResponse
    {
        $account = $this->accountService->createAccount($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Account created successfully.'),
                'data' => $account,
            ]);
        }

        return redirect()->route('finance.accounts.index')
            ->with('success', _trans('common.Account created successfully.'));
    }

    /**
     * Update the specified account.
     */
    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse|JsonResponse
    {
        $updated = $this->accountService->updateAccount($account, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Account updated successfully.'),
                'data' => $updated,
            ]);
        }

        return redirect()->route('finance.accounts.index')
            ->with('success', _trans('common.Account updated successfully.'));
    }

    /**
     * Remove the specified account from storage.
     */
    public function destroy(Request $request, Account $account): RedirectResponse|JsonResponse
    {
        try {
            $this->accountService->deleteAccount($account);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => _trans('common.Account deleted successfully.'),
                ]);
            }

            return redirect()->route('finance.accounts.index')
                ->with('success', _trans('common.Account deleted successfully.'));
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Transfer funds between accounts.
     */
    public function transfer(TransferRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $transaction = $this->accountService->transfer(
                $request->validated(),
                $request->file('attachment')
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => _trans('common.Funds transferred successfully.'),
                    'data' => $transaction,
                ]);
            }

            return redirect()->route('finance.accounts.index')
                ->with('success', _trans('common.Funds transferred successfully.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }
}
