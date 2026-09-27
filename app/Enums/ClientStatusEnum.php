<?php

namespace App\Enums;

enum ClientStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case LEAD = 'lead';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => _trans('common.Active'),
            self::INACTIVE => _trans('common.Inactive'),
            self::LEAD => _trans('common.Lead'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-success bg-opacity-10 text-success',
            self::INACTIVE => 'bg-danger bg-opacity-10 text-danger',
            self::LEAD => 'bg-info bg-opacity-10 text-info',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => '#10b981',
            self::INACTIVE => '#ef4444',
            self::LEAD => '#06b6d4',
        };
    }
}
