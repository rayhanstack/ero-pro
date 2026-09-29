<?php

namespace App\Services\Finance;

use App\Enums\AccountStatusEnum;
use App\Enums\AccountTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Helpers\MediaHelper;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountService
{
    /**
     * Get paginated accounts with search and filters.
     */
    public function getPaginatedAccounts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Account::query();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['type'])) {
            $query->type($filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new account.
     */
    public function createAccount(array $data): Account
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? AccountStatusEnum::ACTIVE;
            $data['opening_balance'] = isset($data['opening_balance']) ? (float) $data['opening_balance'] : 0.00;

            return Account::create($data);
        });
    }

    /**
     * Update an account.
     */
    public function updateAccount(Account $account, array $data): Account
    {
        return DB::transaction(function () use ($account, $data) {
            $account->update($data);

            return $account->fresh();
        });
    }

    /**
     * Delete an account.
     */
    public function deleteAccount(Account $account): bool
    {
        return DB::transaction(function () use ($account) {
            if ($account->transactions()->exists()) {
                throw ValidationException::withMessages([
                    'account' => _trans('common.Cannot delete account with existing transactions. Please archive/deactivate it instead.'),
                ]);
            }

            return (bool) $account->delete();
        });
    }

    /**
     * Transfer funds between two accounts.
     */
    public function transfer(array $data, ?UploadedFile $attachment = null): Transaction
    {
        return DB::transaction(function () use ($data, $attachment) {
            $fromAccount = Account::findOrFail($data['from_account_id']);
            $toAccount = Account::findOrFail($data['to_account_id']);
            $amount = (float) $data['amount'];

            if ($fromAccount->id === $toAccount->id) {
                throw ValidationException::withMessages([
                    'to_account_id' => _trans('common.Source and destination accounts must be different.'),
                ]);
            }

            if ($fromAccount->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => _trans('common.Insufficient funds in :account (Available: :balance).', [
                        'account' => $fromAccount->name,
                        'balance' => currency_format($fromAccount->balance),
                    ]),
                ]);
            }

            $attachmentPath = null;
            if ($attachment) {
                $upload = MediaHelper::upload($attachment, 'finance/transfers');
                $attachmentPath = $upload['file'] ?? null;
            }

            $count = Transaction::whereDate('created_at', today())->count() + 1;
            $trxNumber = sprintf('TRX-%s-%04d', now()->format('Ym'), $count);

            return Transaction::create([
                'transaction_number' => $trxNumber,
                'type' => TransactionTypeEnum::TRANSFER,
                'account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $amount,
                'date' => $data['date'],
                'reference' => $data['reference'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'Transfer',
                'attachment' => $attachmentPath,
                'note' => $data['note'] ?? sprintf('Transfer from %s to %s', $fromAccount->name, $toAccount->name),
                'created_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Calculate summary totals for live balances dashboard.
     */
    public function getAccountsSummary(): array
    {
        $accounts = Account::active()->get();

        $totalBalance = 0.00;
        $bankBalance = 0.00;
        $cashBalance = 0.00;
        $mobileBalance = 0.00;

        foreach ($accounts as $account) {
            $balance = $account->balance;
            $totalBalance += $balance;

            if ($account->type === AccountTypeEnum::BANK) {
                $bankBalance += $balance;
            } elseif ($account->type === AccountTypeEnum::CASH) {
                $cashBalance += $balance;
            } elseif ($account->type === AccountTypeEnum::MOBILE) {
                $mobileBalance += $balance;
            }
        }

        return [
            'total_accounts' => Account::count(),
            'active_accounts' => $accounts->count(),
            'total_balance' => round($totalBalance, 2),
            'bank_balance' => round($bankBalance, 2),
            'cash_balance' => round($cashBalance, 2),
            'mobile_balance' => round($mobileBalance, 2),
        ];
    }
}
