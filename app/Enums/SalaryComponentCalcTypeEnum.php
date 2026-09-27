<?php

namespace App\Enums;

enum SalaryComponentCalcTypeEnum: string
{
    case FIXED = 'fixed';
    case PERCENT_OF_BASIC = 'percent_of_basic';

    public function label(): string
    {
        return match ($this) {
            self::FIXED => _trans('common.Fixed Amount'),
            self::PERCENT_OF_BASIC => _trans('common.Percentage of Basic (% of Basic)'),
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::FIXED => _trans('common.Fixed'),
            self::PERCENT_OF_BASIC => _trans('common.% of Basic'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::FIXED => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
            self::PERCENT_OF_BASIC => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
        };
    }

    public function formatValue(float $value): string
    {
        return match ($this) {
            self::FIXED => currency_format($value),
            self::PERCENT_OF_BASIC => number_format($value, 2) . '%',
        };
    }
}
