<?php

namespace App\Enums;

enum ProjectPriorityEnum: string
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
            self::MEDIUM => 'bg-info bg-opacity-10 text-info',
            self::HIGH => 'bg-warning bg-opacity-10 text-warning',
            self::URGENT => 'bg-danger bg-opacity-10 text-danger',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOW => '#6b7280',
            self::MEDIUM => '#06b6d4',
            self::HIGH => '#f59e0b',
            self::URGENT => '#ef4444',
        };
    }
}
