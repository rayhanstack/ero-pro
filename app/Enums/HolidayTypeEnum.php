<?php

namespace App\Enums;

enum HolidayTypeEnum: string
{
    case PUBLIC = 'public';
    case COMPANY = 'company';

    /**
     * Get translatable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => _trans('common.Public Holiday'),
            self::COMPANY => _trans('common.Company Holiday'),
        };
    }

    /**
     * Get badge css class.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PUBLIC => 'badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1',
            self::COMPANY => 'badge bg-purple-subtle text-indigo border border-indigo-subtle px-2.5 py-1',
        };
    }

    /**
     * Get all values.
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
