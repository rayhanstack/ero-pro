<?php

namespace App\Enums;

enum StatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Get translatable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => _trans('common.Active'),
            self::INACTIVE => _trans('common.Inactive'),
        };
    }

    /**
     * Get bootstrap badge css class.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1',
            self::INACTIVE => 'badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1',
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
