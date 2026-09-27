<?php

namespace App\Enums;

enum MilestoneStatusEnum: string
{
    case INCOMPLETE = 'incomplete';
    case COMPLETE = 'complete';

    public function label(): string
    {
        return match ($this) {
            self::INCOMPLETE => _trans('common.Incomplete'),
            self::COMPLETE => _trans('common.Complete'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::INCOMPLETE => 'bg-warning bg-opacity-10 text-warning',
            self::COMPLETE => 'bg-success bg-opacity-10 text-success',
        };
    }
}
