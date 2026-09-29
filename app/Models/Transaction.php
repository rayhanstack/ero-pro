<?php

namespace App\Models;

use App\Enums\TransactionTypeEnum;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'transaction_number',
        'type',
        'account_id',
        'to_account_id',
        'category_id',
        'category_type',
        'amount',
        'date',
        'reference',
        'payment_method',
        'client_id',
        'project_id',
        'employee_id',
        'invoice_id',
        'payslip_id',
        'attachment',
        'note',
        'created_by',
    ];

    protected $casts = [
        'type' => TransactionTypeEnum::class,
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $prefix = match ($transaction->type?->value ?? $transaction->type) {
                    'income' => 'INC-',
                    'expense' => 'EXP-',
                    'transfer' => 'TRF-',
                    default => 'TXN-',
                };
                $transaction->transaction_number = $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            }
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function incomeCategory(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class, 'category_id');
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class, 'payslip_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', TransactionTypeEnum::INCOME);
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', TransactionTypeEnum::EXPENSE);
    }

    public function scopeTransfer(Builder $query): Builder
    {
        return $query->where('type', TransactionTypeEnum::TRANSFER);
    }

    public function scopeForAccount(Builder $query, int $accountId): Builder
    {
        return $query->where(function ($q) use ($accountId) {
            $q->where('account_id', $accountId)
                ->orWhere('to_account_id', $accountId);
        });
    }

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from && $to) {
            return $query->whereBetween('date', [$from, $to]);
        } elseif ($from) {
            return $query->where('date', '>=', $from);
        } elseif ($to) {
            return $query->where('date', '<=', $to);
        }

        return $query;
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('transaction_number', 'like', "%{$search}%")
                ->orWhere('reference', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%");
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                                  Helpers                                   */
    /* -------------------------------------------------------------------------- */

    public function getCategoryNameAttribute(): string
    {
        if ($this->type === TransactionTypeEnum::INCOME && $this->incomeCategory) {
            return $this->incomeCategory->name;
        } elseif ($this->type === TransactionTypeEnum::EXPENSE && $this->expenseCategory) {
            return $this->expenseCategory->name;
        } elseif ($this->type === TransactionTypeEnum::TRANSFER) {
            return _trans('common.Fund Transfer');
        }

        return _trans('common.General');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return getFilePath($this->attachment, 'default');
    }
}
