<?php

namespace App\Services\Holiday;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HolidayService
{
    /**
     * Get paginated holidays.
     */
    public function getPaginatedHolidays(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Holiday::orderBy('from_date', 'asc');

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['year'])) {
            $query->year((int) $filters['year']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Create a holiday.
     */
    public function createHoliday(array $data): Holiday
    {
        return DB::transaction(function () use ($data) {
            return Holiday::create([
                'title' => $data['title'],
                'from_date' => $data['from_date'],
                'to_date' => $data['to_date'],
                'type' => $data['type'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);
        });
    }

    /**
     * Update a holiday.
     */
    public function updateHoliday(Holiday $holiday, array $data): Holiday
    {
        return DB::transaction(function () use ($holiday, $data) {
            $holiday->update([
                'title' => $data['title'],
                'from_date' => $data['from_date'],
                'to_date' => $data['to_date'],
                'type' => $data['type'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);

            return $holiday;
        });
    }

    /**
     * Delete a holiday.
     */
    public function deleteHoliday(Holiday $holiday): bool
    {
        return DB::transaction(function () use ($holiday) {
            return (bool) $holiday->delete();
        });
    }

    /**
     * Get holidays for a specific month and year for calendar rendering.
     */
    public function getHolidaysForMonth(int $year, int $month): Collection
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        return Holiday::active()
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('from_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('to_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('from_date', '<=', $startOfMonth)
                            ->where('to_date', '>=', $endOfMonth);
                    });
            })
            ->orderBy('from_date')
            ->get();
    }
}
