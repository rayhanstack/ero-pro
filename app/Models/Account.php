<?php

namespace App\Models;

use App\Enums\AccountStatusEnum;
use App\Enums\AccountTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'type',
        'account_number',
        'bank_name',
        'branch',
        'opening_balance',
        'status',
        'note',
    ];

    protected $casts = [
        'type' => AccountTypeEnum::class,
        'status' => AccountStatusEnum::class,
        'opening_balance' => 'decimal:2',
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_account_id')
            ->where('type', TransactionTypeEnum::TRANSFER);
    }

    public function invoicePayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class, 'account_id');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', AccountStatusEnum::ACTIVE);
    }

    public function scopeType(Builder $query, string|AccountTypeEnum $type): Builder
    {
        $val = $type instanceof AccountTypeEnum ? $type->value : $type;

        return $query->where('type', $val);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('account_number', 'like', "%{$search}%")
                ->orWhere('bank_name', 'like', "%{$search}%")
                ->orWhere('branch', 'like', "%{$search}%");
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                               Accessors & Helpers                          */
    /* -------------------------------------------------------------------------- */

    /**
     * Compute live balance accurately using transaction sums.
     */
    public function getBalanceAttribute(): float
    {
        $opening = (float) $this->opening_balance;
        $incomes = (float) $this->transactions()->where('type', TransactionTypeEnum::INCOME)->sum('amount');
        $expenses = (float) $this->transactions()->where('type', TransactionTypeEnum::EXPENSE)->sum('amount');
        $outgoingTransfers = (float) $this->transactions()->where('type', TransactionTypeEnum::TRANSFER)->sum('amount');
        $incomingTransfers = (float) $this->incomingTransfers()->sum('amount');

        return round($opening + $incomes + $incomingTransfers - $expenses - $outgoingTransfers, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return currency_format($this->balance);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->account_number) {
            return "{$this->name} ({$this->account_number})";
        }

        return $this->name;
    }
}
