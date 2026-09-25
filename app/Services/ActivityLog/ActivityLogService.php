<?php

namespace App\Services\ActivityLog;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    /**
     * Get paginated activity logs with filters.
     */
    public function getPaginatedLogs(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return ActivityLog::with('user')
            ->filter($filters)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get list of users who have activities.
     */
    public function getUsersList()
    {
        return User::orderBy('name', 'asc')->get(['id', 'name', 'email']);
    }

    /**
     * Get distinct action types for filter dropdown.
     */
    public function getActionTypes(): array
    {
        return ActivityLog::distinct()->pluck('action')->filter()->values()->toArray();
    }
}
