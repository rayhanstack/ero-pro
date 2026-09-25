<?php

namespace App\Enums;

enum GenderEnum: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';

    /**
     * Get translatable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::MALE => _trans('common.Male'),
            self::FEMALE => _trans('common.Female'),
            self::OTHER => _trans('common.Other'),
        };
    }

    /**
     * Get all values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get options array for selects.
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
