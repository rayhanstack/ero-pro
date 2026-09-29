<?php

namespace App\Enums;

enum AccountTypeEnum: string
{
    case BANK = 'bank';
    case CASH = 'cash';
    case MOBILE = 'mobile';

    public function label(): string
    {
        return match ($this) {
            self::BANK => _trans('common.Bank Account'),
            self::CASH => _trans('common.Cash in Hand'),
            self::MOBILE => _trans('common.Mobile Wallet'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BANK => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
            self::CASH => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            self::MOBILE => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BANK => 'bi-bank2',
            self::CASH => 'bi-cash-stack',
            self::MOBILE => 'bi-phone',
        };
    }
}
