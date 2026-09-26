<?php

namespace App\Enums;

enum HalfDayTypeEnum: string
{
    case FIRST_HALF = 'first_half';
    case SECOND_HALF = 'second_half';

    public function label(): string
    {
        return match ($this) {
            self::FIRST_HALF => _trans('common.First Half'),
            self::SECOND_HALF => _trans('common.Second Half'),
        };
    }
}
