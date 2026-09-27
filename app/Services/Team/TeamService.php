<?php

namespace App\Services\Team;

use App\Enums\TeamStatusEnum;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TeamService
{
    /**
     * Get paginated teams with eager loaded lead and members.
     */
    public function getTeams(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Team::with([
            'lead.employeeDetail.designation',
            'lead.employeeDetail.department',
            'members.employeeDetail.designation',
            'members.employeeDetail.department',
        ])->withCount('members')->latest();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (! empty($filters['lead_id'])) {
            $query->lead($filters['lead_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get team module KPI metrics.
     */
    public function getStats(): array
    {
        return [
            'total' => Team::count(),
            'active' => Team::where('status', TeamStatusEnum::ACTIVE)->count(),
            'inactive' => Team::where('status', TeamStatusEnum::INACTIVE)->count(),
            'total_assigned_members' => TeamMember::distinct('user_id')->count('user_id'),
        ];
    }

    /**
     * Create a new team with optional initial members.
     */
    public function create(array $data): Team
    {
        return DB::transaction(function () use ($data) {
            $memberIds = $data['member_ids'] ?? [];
            unset($data['member_ids']);

            $team = Team::create($data);

            if (! empty($memberIds)) {
                $team->members()->sync($memberIds);
            }

            return $team->fresh(['lead.employeeDetail.designation', 'members.employeeDetail.designation']);
        });
    }

    /**
     * Update an existing team.
     */
    public function update(Team $team, array $data): Team
    {
        return DB::transaction(function () use ($team, $data) {
            $hasMemberIds = array_key_exists('member_ids', $data);
            $memberIds = $data['member_ids'] ?? [];
            unset($data['member_ids']);

            $team->update($data);

            if ($hasMemberIds) {
                $team->members()->sync($memberIds);
            }

            return $team->fresh(['lead.employeeDetail.designation', 'members.employeeDetail.designation']);
        });
    }

    /**
     * Soft delete a team.
     */
    public function delete(Team $team): bool
    {
        return (bool) $team->delete();
    }

    /**
     * Restore a soft-deleted team.
     */
    public function restore(int $id): bool
    {
        $team = Team::onlyTrashed()->findOrFail($id);

        return (bool) $team->restore();
    }

    /**
     * Add member(s) to a team.
     */
    public function addMembers(Team $team, array $memberIds): array
    {
        return $team->members()->syncWithoutDetaching($memberIds);
    }

    /**
     * Remove a member from a team.
     */
    public function removeMember(Team $team, int|User $user): int
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $team->members()->detach($userId);
    }
}
