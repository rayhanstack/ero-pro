<?php

namespace App\Enums;

enum MaritalStatusEnum: string
{
    case SINGLE = 'single';
    case MARRIED = 'married';
    case DIVORCED = 'divorced';
    case WIDOWED = 'widowed';

    /**
     * Get translatable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::SINGLE => _trans('common.Single'),
            self::MARRIED => _trans('common.Married'),
            self::DIVORCED => _trans('common.Divorced'),
            self::WIDOWED => _trans('common.Widowed'),
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
