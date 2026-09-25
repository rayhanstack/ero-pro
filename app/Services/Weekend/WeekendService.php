<?php

namespace App\Services\Weekend;

use App\Models\Weekend;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WeekendService
{
    /**
     * Get all 7 days with weekend flags.
     */
    public function getAllWeekends(): Collection
    {
        return Weekend::orderBy('day_of_week')->get();
    }

    /**
     * Update weekend days in bulk.
     *
     * @param  array<int>  $selectedDays  Array of day_of_week integers (0-6)
     */
    public function updateWeekends(array $selectedDays): void
    {
        DB::transaction(function () use ($selectedDays) {
            for ($day = 0; $day <= 6; $day++) {
                $isWeekend = in_array($day, $selectedDays, false);
                Weekend::where('day_of_week', $day)->update(['is_weekend' => $isWeekend]);
            }
        });
    }
}
