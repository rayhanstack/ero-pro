<?php

namespace App\Enums;

enum SalaryComponentTypeEnum: string
{
    case EARNING = 'earning';
    case DEDUCTION = 'deduction';

    public function label(): string
    {
        return match ($this) {
            self::EARNING => _trans('common.Earning / Allowance'),
            self::DEDUCTION => _trans('common.Deduction'),
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::EARNING => _trans('common.Earning'),
            self::DEDUCTION => _trans('common.Deduction'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::EARNING => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            self::DEDUCTION => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EARNING => 'bi-plus-circle',
            self::DEDUCTION => 'bi-dash-circle',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EARNING => 'success',
            self::DEDUCTION => 'danger',
        };
    }
}
