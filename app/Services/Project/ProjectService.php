<?php

namespace App\Services\Project;

use App\Enums\MilestoneStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectMilestone;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectService
{
    /**
     * Get paginated projects with eager loading and filters.
     */
    public function getProjects(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Project::with([
            'client',
            'currency',
            'manager.employeeDetail.designation',
            'members.employeeDetail.designation',
        ])->withCount(['members', 'milestones', 'files'])->latest();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (! empty($filters['client_id'])) {
            $query->client($filters['client_id']);
        }

        if (! empty($filters['priority'])) {
            $query->priority($filters['priority']);
        }

        if (! empty($filters['manager_id'])) {
            $query->manager($filters['manager_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get summary KPI statistics.
     */
    public function getStats(): array
    {
        return [
            'total' => Project::count(),
            'active' => Project::where('status', ProjectStatusEnum::ACTIVE)->count(),
            'completed' => Project::where('status', ProjectStatusEnum::COMPLETED)->count(),
            'total_budget' => Project::sum('budget') ?? 0.00,
        ];
    }

    /**
     * Create a new project with optional member and team assignments.
     */
    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $memberIds = $data['member_ids'] ?? $data['members'] ?? [];
            $teamIds = $data['team_ids'] ?? $data['teams'] ?? [];
            unset($data['member_ids'], $data['members'], $data['team_ids'], $data['teams']);

            if (empty($data['code'])) {
                $data['code'] = Project::generateCode();
            }

            $project = Project::create($data);

            // Collect all employee IDs to attach
            $allEmployeeIds = (array) $memberIds;

            if (! empty($teamIds)) {
                $teamMemberIds = DB::table('team_members')
                    ->whereIn('team_id', $teamIds)
                    ->pluck('user_id')
                    ->toArray();

                $teamLeadIds = Team::whereIn('id', $teamIds)
                    ->whereNotNull('lead_id')
                    ->pluck('lead_id')
                    ->toArray();

                $allEmployeeIds = array_unique(array_merge($allEmployeeIds, $teamMemberIds, $teamLeadIds));
            }

            if (! empty($allEmployeeIds)) {
                $attachData = [];
                foreach ($allEmployeeIds as $empId) {
                    $attachData[$empId] = ['role' => 'member'];
                }
                $project->members()->sync($attachData);
            }

            return $project->fresh(['client', 'currency', 'manager', 'members']);
        });
    }

    /**
     * Update an existing project.
     */
    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $hasMemberIds = array_key_exists('member_ids', $data) || array_key_exists('members', $data);
            $memberIds = $data['member_ids'] ?? $data['members'] ?? [];
            $teamIds = $data['team_ids'] ?? $data['teams'] ?? [];
            unset($data['member_ids'], $data['members'], $data['team_ids'], $data['teams']);

            $project->update($data);

            if ($hasMemberIds || ! empty($teamIds)) {
                $allEmployeeIds = (array) $memberIds;

                if (! empty($teamIds)) {
                    $teamMemberIds = DB::table('team_members')
                        ->whereIn('team_id', $teamIds)
                        ->pluck('user_id')
                        ->toArray();

                    $teamLeadIds = Team::whereIn('id', $teamIds)
                        ->whereNotNull('lead_id')
                        ->pluck('lead_id')
                        ->toArray();

                    $allEmployeeIds = array_unique(array_merge($allEmployeeIds, $teamMemberIds, $teamLeadIds));
                }

                $attachData = [];
                foreach ($allEmployeeIds as $empId) {
                    $attachData[$empId] = ['role' => 'member'];
                }
                $project->members()->sync($attachData);
            }

            return $project->fresh(['client', 'currency', 'manager', 'members']);
        });
    }

    /**
     * Soft delete a project.
     */
    public function delete(Project $project): bool
    {
        return (bool) $project->delete();
    }

    /**
     * Restore a soft-deleted project.
     */
    public function restore(int $id): bool
    {
        $project = Project::onlyTrashed()->findOrFail($id);

        return (bool) $project->restore();
    }

    /**
     * Recalculate project progress based on task completion (done / total).
     * To be called by Task module later.
     */
    public function recalculateProgress(Project $project): int
    {
        // Check if project has tasks relation and records
        if (method_exists($project, 'tasks') && $project->tasks()->count() > 0) {
            $totalTasks = $project->tasks()->count();
            $completedTasks = $project->tasks()->where('status', 'completed')->count();
            $progress = (int) round(($completedTasks / $totalTasks) * 100);

            $project->update(['progress' => min(100, max(0, $progress))]);

            return $project->progress;
        }

        // Fallback: If milestones exist, compute from completed milestones
        if ($project->milestones()->count() > 0) {
            $totalMilestones = $project->milestones()->count();
            $completedMilestones = $project->milestones()->where('status', MilestoneStatusEnum::COMPLETE)->count();
            $progress = (int) round(($completedMilestones / $totalMilestones) * 100);

            $project->update(['progress' => min(100, max(0, $progress))]);

            return $project->progress;
        }

        return $project->progress;
    }

    /**
     * Add member(s) to a project.
     */
    public function addMembers(Project $project, array $employeeIds, string $role = 'member'): void
    {
        $attachData = [];
        foreach ($employeeIds as $empId) {
            $attachData[$empId] = ['role' => $role];
        }

        $project->members()->syncWithoutDetaching($attachData);
    }

    /**
     * Remove a member from a project.
     */
    public function removeMember(Project $project, int|User $employee): int
    {
        $employeeId = $employee instanceof User ? $employee->id : $employee;

        return $project->members()->detach($employeeId);
    }

    /**
     * Assign an entire team to a project.
     */
    public function assignTeam(Project $project, int|Team $team): void
    {
        $teamModel = $team instanceof Team ? $team : Team::with('members')->findOrFail($team);
        $memberIds = $teamModel->members->pluck('id')->toArray();

        if ($teamModel->lead_id) {
            $memberIds[] = $teamModel->lead_id;
        }

        $this->addMembers($project, array_unique($memberIds), 'team_member');
    }

    /**
     * Add a milestone to the project.
     */
    public function addMilestone(Project $project, array $data): ProjectMilestone
    {
        $milestone = $project->milestones()->create($data);
        $this->recalculateProgress($project);

        return $milestone;
    }

    /**
     * Update a milestone.
     */
    public function updateMilestone(ProjectMilestone $milestone, array $data): ProjectMilestone
    {
        $milestone->update($data);
        $this->recalculateProgress($milestone->project);

        return $milestone;
    }

    /**
     * Toggle a milestone complete status.
     */
    public function toggleMilestone(ProjectMilestone $milestone): ProjectMilestone
    {
        $newStatus = $milestone->status === MilestoneStatusEnum::COMPLETE
            ? MilestoneStatusEnum::INCOMPLETE
            : MilestoneStatusEnum::COMPLETE;

        $milestone->update(['status' => $newStatus]);
        $this->recalculateProgress($milestone->project);

        return $milestone;
    }

    /**
     * Delete a milestone.
     */
    public function deleteMilestone(ProjectMilestone $milestone): bool
    {
        $project = $milestone->project;
        $res = (bool) $milestone->delete();
        $this->recalculateProgress($project);

        return $res;
    }

    /**
     * Upload and attach a file to the project.
     */
    public function addFile(Project $project, UploadedFile $file, ?int $userId = null): ProjectFile
    {
        $originalName = $file->getClientOriginalName();
        $fileSize = $this->formatBytes($file->getSize());
        $fileType = strtolower($file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'file'));
        $path = $file->store("projects/{$project->id}", 'public');

        return $project->files()->create([
            'user_id' => $userId,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_size' => $fileSize,
            'file_type' => $fileType,
        ]);
    }

    /**
     * Delete a project file.
     */
    public function deleteFile(ProjectFile $file): bool
    {
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        return (bool) $file->delete();
    }

    /**
     * Helper to format file size in human-readable bytes.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
