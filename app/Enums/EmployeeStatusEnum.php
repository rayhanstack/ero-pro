<?php

namespace App\Enums;

enum EmployeeStatusEnum: string
{
    case ACTIVE = 'active';
    case ON_LEAVE = 'on_leave';
    case RESIGNED = 'resigned';
    case TERMINATED = 'terminated';

    /**
     * Get translatable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => _trans('common.Active'),
            self::ON_LEAVE => _trans('common.On Leave'),
            self::RESIGNED => _trans('common.Resigned'),
            self::TERMINATED => _trans('common.Terminated'),
        };
    }

    /**
     * Get bootstrap badge css class.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1',
            self::ON_LEAVE => 'badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1',
            self::RESIGNED => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1',
            self::TERMINATED => 'badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1',
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
