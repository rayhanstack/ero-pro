<?php

namespace App\Enums;

enum MeetingTypeEnum: string
{
    case IN_PERSON = 'in_person';
    case ONLINE = 'online';
    case HYBRID = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::IN_PERSON => _trans('common.In-Person'),
            self::ONLINE => _trans('common.Online (Virtual)'),
            self::HYBRID => _trans('common.Hybrid'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::IN_PERSON => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
            self::ONLINE => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
            self::HYBRID => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::IN_PERSON => 'bi-building',
            self::ONLINE => 'bi-camera-video',
            self::HYBRID => 'bi-laptop',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN_PERSON => '#4f46e5',
            self::ONLINE => '#0ea5e9',
            self::HYBRID => '#8b5cf6',
        };
    }
}
