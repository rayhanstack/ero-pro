<?php

namespace App\Enums;

enum AttendanceSourceEnum: string
{
    case WEB = 'web';
    case MANUAL = 'manual';
    case IMPORT = 'import';

    /**
     * Get translatable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::WEB => _trans('common.Web / Online'),
            self::MANUAL => _trans('common.Manual (HR)'),
            self::IMPORT => _trans('common.Device Import'),
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
