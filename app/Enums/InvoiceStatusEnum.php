<?php

namespace App\Enums;

enum InvoiceStatusEnum: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => _trans('common.Draft'),
            self::SENT => _trans('common.Sent'),
            self::PARTIAL => _trans('common.Partially Paid'),
            self::PAID => _trans('common.Paid'),
            self::OVERDUE => _trans('common.Overdue'),
            self::CANCELLED => _trans('common.Cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
            self::SENT => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
            self::PARTIAL => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
            self::PAID => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            self::OVERDUE => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
            self::CANCELLED => 'bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'bi-pencil',
            self::SENT => 'bi-send',
            self::PARTIAL => 'bi-pie-chart-fill',
            self::PAID => 'bi-check-circle-fill',
            self::OVERDUE => 'bi-exclamation-triangle-fill',
            self::CANCELLED => 'bi-x-circle-fill',
        };
    }
}
