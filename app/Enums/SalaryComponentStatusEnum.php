<?php

namespace App\Enums;

enum SalaryComponentStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => _trans('common.Active'),
            self::INACTIVE => _trans('common.Inactive'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            self::INACTIVE => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
        };
    }
}
