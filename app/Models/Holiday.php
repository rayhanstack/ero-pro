<?php

namespace App\Models;

use App\Enums\HolidayTypeEnum;
use App\Enums\StatusEnum;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Holiday extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'from_date',
        'to_date',
        'type',
        'description',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'type' => HolidayTypeEnum::class,
            'status' => StatusEnum::class,
        ];
    }

    /**
     * Total days span of holiday.
     */
    public function getDaysCountAttribute(): int
    {
        if (! $this->from_date || ! $this->to_date) {
            return 1;
        }

        return $this->from_date->diffInDays($this->to_date) + 1;
    }

    /**
     * Scope query to active holidays.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', StatusEnum::ACTIVE);
    }

    /**
     * Scope query by year.
     */
    public function scopeYear(Builder $query, ?int $year): Builder
    {
        if (empty($year)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($year) {
            $q->whereYear('from_date', $year)
                ->orWhereYear('to_date', $year);
        });
    }

    /**
     * Scope query to search term.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Model boot hooks.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('calendar.holidays');
        });

        static::deleted(function () {
            Cache::forget('calendar.holidays');
        });
    }
}
