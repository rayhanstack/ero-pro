<?php

namespace App\Enums;

enum MeetingAttendeeResponseEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => _trans('common.Awaiting Response'),
            self::ACCEPTED => _trans('common.Accepted'),
            self::DECLINED => _trans('common.Declined'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
            self::ACCEPTED => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            self::DECLINED => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'bi-hourglass-split',
            self::ACCEPTED => 'bi-check-circle-fill',
            self::DECLINED => 'bi-x-circle-fill',
        };
    }
}
