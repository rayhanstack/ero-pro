<?php

namespace App\Enums;

enum PayslipStatusEnum: string
{
    case DRAFT = 'draft';
    case APPROVED = 'approved';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => _trans('common.Draft'),
            self::APPROVED => _trans('common.Approved'),
            self::PAID => _trans('common.Paid'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
            self::APPROVED => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
            self::PAID => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'bi-file-earmark-text',
            self::APPROVED => 'bi-shield-check',
            self::PAID => 'bi-cash-coin',
        };
    }
}
