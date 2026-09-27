<?php

namespace App\Services\Payroll;

use App\Enums\SalaryComponentStatusEnum;
use App\Enums\SalaryComponentTypeEnum;
use App\Models\SalaryComponent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalaryComponentService
{
    /**
     * Get paginated salary components with filters.
     */
    public function getPaginatedComponents(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildFilteredQuery($filters);

        return $query->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get all active components grouped by type.
     */
    public function getActiveComponents(): Collection
    {
        return SalaryComponent::active()->orderBy('type')->orderBy('name')->get();
    }

    /**
     * Get statistics summary for salary components.
     */
    public function getStats(): array
    {
        return [
            'total' => SalaryComponent::count(),
            'earnings' => SalaryComponent::earnings()->count(),
            'deductions' => SalaryComponent::deductions()->count(),
            'active' => SalaryComponent::active()->count(),
        ];
    }

    /**
     * Create a new salary component.
     */
    public function create(array $data): SalaryComponent
    {
        return DB::transaction(function () use ($data) {
            return SalaryComponent::create($data);
        });
    }

    /**
     * Update an existing salary component.
     */
    public function update(SalaryComponent $component, array $data): SalaryComponent
    {
        return DB::transaction(function () use ($component, $data) {
            $component->update($data);

            return $component->fresh();
        });
    }

    /**
     * Delete a salary component (soft delete).
     */
    public function delete(SalaryComponent $component): bool
    {
        return DB::transaction(function () use ($component) {
            // Also detach from employee salary components mapping if needed, or leave intact for history
            return (bool) $component->delete();
        });
    }

    /**
     * Build filtered Eloquent query.
     */
    protected function buildFilteredQuery(array $filters = []): Builder
    {
        $query = SalaryComponent::query()
            ->withCount('employeeSalaryComponents');

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['calc_type'])) {
            $query->where('calc_type', $filters['calc_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }
}
