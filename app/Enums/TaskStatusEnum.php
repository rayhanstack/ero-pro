<?php

namespace App\Enums;

enum TaskStatusEnum: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case DONE = 'done';

    public function label(): string
    {
        return match ($this) {
            self::TODO => _trans('common.To Do'),
            self::IN_PROGRESS => _trans('common.In Progress'),
            self::REVIEW => _trans('common.In Review'),
            self::DONE => _trans('common.Done'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::TODO => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
            self::IN_PROGRESS => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
            self::REVIEW => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
            self::DONE => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
        };
    }

    public function borderClass(): string
    {
        return match ($this) {
            self::TODO => 'border-info',
            self::IN_PROGRESS => 'border-warning',
            self::REVIEW => 'border-primary',
            self::DONE => 'border-success',
        };
    }

    public function dotClass(): string
    {
        return match ($this) {
            self::TODO => 'bg-info',
            self::IN_PROGRESS => 'bg-warning',
            self::REVIEW => 'bg-primary',
            self::DONE => 'bg-success',
        };
    }
}
