<?php

namespace App\Enums;

enum EmploymentTypeEnum: string
{
    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case CONTRACT = 'contract';
    case INTERN = 'intern';
    case FREELANCE = 'freelance';

    /**
     * Get translatable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::FULL_TIME => _trans('common.Full Time'),
            self::PART_TIME => _trans('common.Part Time'),
            self::CONTRACT => _trans('common.Contract'),
            self::INTERN => _trans('common.Intern'),
            self::FREELANCE => _trans('common.Freelance'),
        };
    }

    /**
     * Get badge css class.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::FULL_TIME => 'badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1',
            self::PART_TIME => 'badge bg-info-subtle text-info border border-info-subtle px-2 py-1',
            self::CONTRACT => 'badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1',
            self::INTERN => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1',
            self::FREELANCE => 'badge bg-dark-subtle text-dark border border-dark-subtle px-2 py-1',
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
