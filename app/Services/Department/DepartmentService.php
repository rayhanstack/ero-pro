<?php

namespace App\Services\Department;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    /**
     * Get paginated departments with search and status filters.
     */
    public function getPaginatedDepartments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Department::withCount('designations')
            ->orderBy('id', 'desc');

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new department.
     */
    public function createDepartment(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            return Department::create([
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'head_id' => $data['head_id'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);
        });
    }

    /**
     * Update an existing department.
     */
    public function updateDepartment(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data) {
            $department->update([
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'head_id' => $data['head_id'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);

            return $department;
        });
    }

    /**
     * Delete a department (Soft delete).
     */
    public function deleteDepartment(Department $department): bool
    {
        return DB::transaction(function () use ($department) {
            return (bool) $department->delete();
        });
    }

    /**
     * Get all active departments for selection.
     */
    public function getAllActiveDepartments(): Collection
    {
        return Department::active()->orderBy('name')->get();
    }
}
