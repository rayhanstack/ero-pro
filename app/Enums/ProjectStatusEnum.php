<?php

namespace App\Enums;

enum ProjectStatusEnum: string
{
    case PLANNING = 'planning';
    case ACTIVE = 'active';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PLANNING => _trans('common.Planning / Draft'),
            self::ACTIVE => _trans('common.In Progress'),
            self::ON_HOLD => _trans('common.On Hold'),
            self::COMPLETED => _trans('common.Completed'),
            self::CANCELLED => _trans('common.Cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PLANNING => 'bg-secondary bg-opacity-10 text-secondary',
            self::ACTIVE => 'bg-primary bg-opacity-10 text-primary',
            self::ON_HOLD => 'bg-warning bg-opacity-10 text-warning',
            self::COMPLETED => 'bg-success bg-opacity-10 text-success',
            self::CANCELLED => 'bg-danger bg-opacity-10 text-danger',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PLANNING => '#64748b',
            self::ACTIVE => '#4f46e5',
            self::ON_HOLD => '#f59e0b',
            self::COMPLETED => '#10b981',
            self::CANCELLED => '#ef4444',
        };
    }
}
