<?php

namespace App\Enums;

enum AttendanceStatusEnum: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case ABSENT = 'absent';
    case LEAVE = 'leave';
    case HOLIDAY = 'holiday';
    case WEEKEND = 'weekend';
    case HALF_DAY = 'half_day';

    /**
     * Get translatable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PRESENT => _trans('common.Present'),
            self::LATE => _trans('common.Late'),
            self::ABSENT => _trans('common.Absent'),
            self::LEAVE => _trans('common.Leave'),
            self::HOLIDAY => _trans('common.Holiday'),
            self::WEEKEND => _trans('common.Weekend'),
            self::HALF_DAY => _trans('common.Half Day'),
        };
    }

    /**
     * Get short code for grid/calendar cells.
     */
    public function shortCode(): string
    {
        return match ($this) {
            self::PRESENT => 'P',
            self::LATE => 'L',
            self::ABSENT => 'A',
            self::LEAVE => 'Lv',
            self::HOLIDAY => 'H',
            self::WEEKEND => 'W',
            self::HALF_DAY => 'HD',
        };
    }

    /**
     * Get bootstrap badge css class.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PRESENT => 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1',
            self::LATE => 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1',
            self::ABSENT => 'badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1',
            self::LEAVE => 'badge bg-info-subtle text-info border border-info-subtle px-2 py-1',
            self::HOLIDAY => 'badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1',
            self::WEEKEND => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1',
            self::HALF_DAY => 'badge bg-purple-subtle text-purple border border-purple-subtle px-2 py-1',
        };
    }

    /**
     * Get grid cell class for monthly matrix.
     */
    public function gridCellClass(): string
    {
        return match ($this) {
            self::PRESENT => 'bg-success text-white',
            self::LATE => 'bg-warning text-dark',
            self::ABSENT => 'bg-danger text-white',
            self::LEAVE => 'bg-info text-dark',
            self::HOLIDAY => 'bg-primary text-white',
            self::WEEKEND => 'bg-secondary-subtle text-muted',
            self::HALF_DAY => 'bg-info-subtle text-dark',
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
