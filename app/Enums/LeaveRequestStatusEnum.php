<?php

namespace App\Enums;

enum LeaveRequestStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => _trans('common.Pending'),
            self::APPROVED => _trans('common.Approved'),
            self::REJECTED => _trans('common.Rejected'),
            self::CANCELLED => _trans('common.Cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning text-dark',
            self::APPROVED => 'bg-success',
            self::REJECTED => 'bg-danger',
            self::CANCELLED => 'bg-secondary',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => '#f59e0b',
            self::APPROVED => '#10b981',
            self::REJECTED => '#ef4444',
            self::CANCELLED => '#6b7280',
        };
    }
}
