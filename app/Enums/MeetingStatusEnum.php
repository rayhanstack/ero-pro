<?php

namespace App\Enums;

enum MeetingStatusEnum: string
{
    case SCHEDULED = 'scheduled';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => _trans('common.Scheduled'),
            self::COMPLETED => _trans('common.Completed'),
            self::CANCELLED => _trans('common.Cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::SCHEDULED => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
            self::COMPLETED => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            self::CANCELLED => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SCHEDULED => '#4f46e5',
            self::COMPLETED => '#10b981',
            self::CANCELLED => '#ef4444',
        };
    }
}
