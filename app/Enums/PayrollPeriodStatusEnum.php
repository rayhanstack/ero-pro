<?php

namespace App\Enums;

enum PayrollPeriodStatusEnum: string
{
    case DRAFT = 'draft';
    case PROCESSED = 'processed';
    case PAID = 'paid';
    case LOCKED = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => _trans('common.Draft'),
            self::PROCESSED => _trans('common.Processed'),
            self::PAID => _trans('common.Paid'),
            self::LOCKED => _trans('common.Locked'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
            self::PROCESSED => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
            self::PAID => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            self::LOCKED => 'bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'bi-pencil',
            self::PROCESSED => 'bi-gear-wide-connected',
            self::PAID => 'bi-check-circle-fill',
            self::LOCKED => 'bi-lock-fill',
        };
    }
}
