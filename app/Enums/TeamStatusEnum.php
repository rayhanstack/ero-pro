<?php

namespace App\Enums;

enum TeamStatusEnum: string
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
            self::ACTIVE => 'bg-success bg-opacity-10 text-success',
            self::INACTIVE => 'bg-danger bg-opacity-10 text-danger',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => '#10b981',
            self::INACTIVE => '#ef4444',
        };
    }
}
