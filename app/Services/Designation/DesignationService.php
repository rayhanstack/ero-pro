<?php

namespace App\Services\Designation;

use App\Models\Designation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DesignationService
{
    /**
     * Get paginated designations with search, department, and status filters.
     */
    public function getPaginatedDesignations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Designation::with('department')
            ->orderBy('id', 'desc');

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new designation.
     */
    public function createDesignation(array $data): Designation
    {
        return DB::transaction(function () use ($data) {
            return Designation::create([
                'name' => $data['name'],
                'department_id' => $data['department_id'],
                'level' => $data['level'] ?? 1,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);
        });
    }

    /**
     * Update an existing designation.
     */
    public function updateDesignation(Designation $designation, array $data): Designation
    {
        return DB::transaction(function () use ($designation, $data) {
            $designation->update([
                'name' => $data['name'],
                'department_id' => $data['department_id'],
                'level' => $data['level'] ?? 1,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);

            return $designation;
        });
    }

    /**
     * Delete a designation (Soft delete).
     */
    public function deleteDesignation(Designation $designation): bool
    {
        return DB::transaction(function () use ($designation) {
            return (bool) $designation->delete();
        });
    }

    /**
     * Get active designations by department.
     */
    public function getDesignationsByDepartment(int $departmentId): Collection
    {
        return Designation::active()
            ->where('department_id', $departmentId)
            ->orderBy('name')
            ->get();
    }
}
