<?php

namespace App\Services\Hr;

use App\Models\Holiday;
use App\Models\Weekend;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;

class CalendarService
{
    /**
     * Check if a given date falls on a configured weekend.
     */
    public function isWeekend(string|DateTimeInterface $date): bool
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $dayOfWeek = (int) $carbon->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

        $weekendDays = Cache::rememberForever('calendar.weekends', function () {
            return Weekend::where('is_weekend', true)->pluck('day_of_week')->toArray();
        });

        return in_array($dayOfWeek, $weekendDays, true);
    }

    /**
     * Check if a given date falls on an active holiday.
     */
    public function isHoliday(string|DateTimeInterface $date): bool
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $formattedDate = $carbon->format('Y-m-d');

        $holidayRanges = Cache::rememberForever('calendar.holidays', function () {
            return Holiday::active()
                ->get(['from_date', 'to_date'])
                ->map(fn ($h) => [
                    'from' => $h->from_date->format('Y-m-d'),
                    'to' => $h->to_date->format('Y-m-d'),
                ])
                ->toArray();
        });

        foreach ($holidayRanges as $range) {
            if ($formattedDate >= $range['from'] && $formattedDate <= $range['to']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Count total working days between two dates (inclusive), excluding weekends and holidays.
     */
    public function workingDaysBetween(string|DateTimeInterface $from, string|DateTimeInterface $to): int
    {
        $startDate = $from instanceof Carbon ? $from->copy()->startOfDay() : Carbon::parse($from)->startOfDay();
        $endDate = $to instanceof Carbon ? $to->copy()->startOfDay() : Carbon::parse($to)->startOfDay();

        if ($startDate->gt($endDate)) {
            return 0;
        }

        $period = CarbonPeriod::create($startDate, '1 day', $endDate);
        $workingDays = 0;

        foreach ($period as $date) {
            if (! $this->isWeekend($date) && ! $this->isHoliday($date)) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    /**
     * Clear all cached calendar data.
     */
    public function clearCache(): void
    {
        Cache::forget('calendar.weekends');
        Cache::forget('calendar.holidays');
    }
}
