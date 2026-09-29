<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::INCOME => _trans('common.Income'),
            self::EXPENSE => _trans('common.Expense'),
            self::TRANSFER => _trans('common.Transfer'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::INCOME => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            self::EXPENSE => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
            self::TRANSFER => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::INCOME => 'bi-arrow-down-left-circle-fill',
            self::EXPENSE => 'bi-arrow-up-right-circle-fill',
            self::TRANSFER => 'bi-arrow-left-right',
        };
    }
}
