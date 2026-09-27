<?php

namespace App\Enums;

enum RegularizationStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * Get translatable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => _trans('common.Pending'),
            self::APPROVED => _trans('common.Approved'),
            self::REJECTED => _trans('common.Rejected'),
        };
    }

    /**
     * Get bootstrap badge css class.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1',
            self::APPROVED => 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1',
            self::REJECTED => 'badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1',
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
