<?php

namespace App\Services\Shift;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ShiftService
{
    /**
     * Get paginated shifts.
     */
    public function getPaginatedShifts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Shift::orderBy('id', 'desc');

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Create a shift.
     */
    public function createShift(array $data): Shift
    {
        return DB::transaction(function () use ($data) {
            return Shift::create([
                'name' => $data['name'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'grace_minutes' => $data['grace_minutes'] ?? 0,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);
        });
    }

    /**
     * Update a shift.
     */
    public function updateShift(Shift $shift, array $data): Shift
    {
        return DB::transaction(function () use ($shift, $data) {
            $shift->update([
                'name' => $data['name'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'grace_minutes' => $data['grace_minutes'] ?? 0,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);

            return $shift;
        });
    }

    /**
     * Delete a shift.
     */
    public function deleteShift(Shift $shift): bool
    {
        return DB::transaction(function () use ($shift) {
            return (bool) $shift->delete();
        });
    }

    /**
     * Get all active shifts.
     */
    public function getAllActiveShifts(): Collection
    {
        return Shift::active()->orderBy('name')->get();
    }
}
