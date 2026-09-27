<?php

namespace App\Enums;

enum TaskPriorityEnum: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::LOW => _trans('common.Low'),
            self::MEDIUM => _trans('common.Medium'),
            self::HIGH => _trans('common.High'),
            self::URGENT => _trans('common.Urgent'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::LOW => 'bg-secondary bg-opacity-10 text-secondary',
            self::MEDIUM => 'bg-warning bg-opacity-10 text-dark',
            self::HIGH => 'bg-danger bg-opacity-10 text-danger',
            self::URGENT => 'bg-danger text-white',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOW => '#64748b',
            self::MEDIUM => '#f59e0b',
            self::HIGH => '#ef4444',
            self::URGENT => '#dc2626',
        };
    }
}
