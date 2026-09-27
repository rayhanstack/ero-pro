<?php

namespace App\Http\Controllers\Admin\Project;

use App\Enums\MilestoneStatusEnum;
use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\AddProjectMembersRequest;
use App\Http\Requests\Project\StoreMilestoneRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\UploadProjectFileRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectMilestone;
use App\Models\Team;
use App\Models\User;
use App\Services\Project\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    /**
     * Display a listing of projects with grid/list modes and filters.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'client_id', 'priority', 'manager_id']);
        $viewMode = $request->input('view', 'grid');
        $perPage = $viewMode === 'list' ? 15 : 12;

        $projects = $this->projectService->getProjects($filters, $perPage);
        $stats = $this->projectService->getStats();
        $statuses = ProjectStatusEnum::cases();
        $priorities = ProjectPriorityEnum::cases();
        $clients = Client::active()->orderBy('company_name')->get();
        $currencies = Currency::all();
        $employees = User::with('employeeDetail.designation')->active()->get();
        $teams = Team::active()->with('members')->get();

        return view('admin.project.index', compact(
            'projects',
            'stats',
            'filters',
            'viewMode',
            'statuses',
            'priorities',
            'clients',
            'currencies',
            'employees',
            'teams'
        ));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        $clients = Client::active()->orderBy('company_name')->get();
        $currencies = Currency::all();
        $employees = User::with(['employeeDetail.designation', 'employeeDetail.department'])->active()->get();
        $teams = Team::active()->with('members')->get();
        $statuses = ProjectStatusEnum::cases();
        $priorities = ProjectPriorityEnum::cases();

        return view('admin.project.create', compact(
            'clients',
            'currencies',
            'employees',
            'teams',
            'statuses',
            'priorities'
        ));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse|JsonResponse
    {
        $project = $this->projectService->create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Project created successfully.'),
                'data' => $project,
            ]);
        }

        return redirect()->route('projects.index')->with('success', _trans('common.Project created successfully.'));
    }

    /**
     * Display the specified project profile with 6 tabs.
     */
    public function show(Project $project): View
    {
        $project->load([
            'client.country',
            'currency',
            'manager.employeeDetail.designation',
            'manager.employeeDetail.department',
            'members.employeeDetail.designation',
            'members.employeeDetail.department',
            'milestones',
            'files.uploader',
        ]);

        $existingMemberIds = $project->members->pluck('id')->toArray();
        $availableEmployees = User::with(['employeeDetail.designation', 'employeeDetail.department'])
            ->active()
            ->whereNotIn('id', $existingMemberIds)
            ->get();

        $teams = Team::active()->with('members')->get();
        $milestoneStatuses = MilestoneStatusEnum::cases();

        $activities = ActivityLog::where('subject_type', Project::class)
            ->where('subject_id', $project->id)
            ->latest()
            ->take(20)
            ->get();

        return view('admin.project.show', compact(
            'project',
            'availableEmployees',
            'teams',
            'milestoneStatuses',
            'activities'
        ));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project): View
    {
        $project->load(['client', 'currency', 'manager', 'members']);
        $clients = Client::active()->orderBy('company_name')->get();
        $currencies = Currency::all();
        $employees = User::with(['employeeDetail.designation', 'employeeDetail.department'])->active()->get();
        $teams = Team::active()->with('members')->get();
        $statuses = ProjectStatusEnum::cases();
        $priorities = ProjectPriorityEnum::cases();
        $selectedMemberIds = $project->members->pluck('id')->toArray();

        return view('admin.project.edit', compact(
            'project',
            'clients',
            'currencies',
            'employees',
            'teams',
            'statuses',
            'priorities',
            'selectedMemberIds'
        ));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse|JsonResponse
    {
        $this->projectService->update($project, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Project updated successfully.'),
                'data' => $project,
            ]);
        }

        return redirect()->route('projects.show', $project)->with('success', _trans('common.Project updated successfully.'));
    }

    /**
     * Remove the specified project from storage (Soft Delete).
     */
    public function destroy(Project $project): RedirectResponse|JsonResponse
    {
        $this->projectService->delete($project);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Project deleted successfully.'),
            ]);
        }

        return redirect()->route('projects.index')->with('success', _trans('common.Project deleted successfully.'));
    }

    /**
     * Restore a soft-deleted project.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->projectService->restore($id);

        return redirect()->route('projects.index')->with('success', _trans('common.Project restored successfully.'));
    }

    /**
     * Add member(s) to a project.
     */
    public function addMembers(AddProjectMembersRequest $request, Project $project): RedirectResponse
    {
        $employeeIds = $request->input('employee_ids') ?? $request->input('members') ?? [];

        $this->projectService->addMembers(
            $project,
            (array) $employeeIds,
            $request->input('role', 'member')
        );

        return back()->with('success', _trans('common.Members added to project successfully.'));
    }

    /**
     * Remove a member from a project.
     */
    public function removeMember(Project $project, User $user): RedirectResponse
    {
        $this->projectService->removeMember($project, $user);

        return back()->with('success', _trans('common.Member removed from project successfully.'));
    }

    /**
     * Assign a team to a project.
     */
    public function assignTeam(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
        ]);

        $this->projectService->assignTeam($project, $request->input('team_id'));

        return back()->with('success', _trans('common.Team assigned to project successfully.'));
    }

    /**
     * Store a milestone for the project.
     */
    public function storeMilestone(StoreMilestoneRequest $request, Project $project): RedirectResponse
    {
        $this->projectService->addMilestone($project, $request->validated());

        return back()->with('success', _trans('common.Milestone added successfully.'));
    }

    /**
     * Toggle milestone completion status.
     */
    public function toggleMilestone(ProjectMilestone $milestone): RedirectResponse
    {
        $this->projectService->toggleMilestone($milestone);

        return back()->with('success', _trans('common.Milestone status updated.'));
    }

    /**
     * Delete a milestone.
     */
    public function deleteMilestone(ProjectMilestone $milestone): RedirectResponse
    {
        $this->projectService->deleteMilestone($milestone);

        return back()->with('success', _trans('common.Milestone deleted successfully.'));
    }

    /**
     * Upload a file attachment for the project.
     */
    public function uploadFile(UploadProjectFileRequest $request, Project $project): RedirectResponse
    {
        $this->projectService->addFile($project, $request->file('file'), Auth::id());

        return back()->with('success', _trans('common.File uploaded successfully.'));
    }

    /**
     * Download an attached project file.
     */
    public function downloadFile(ProjectFile $file): StreamedResponse
    {
        if (! Storage::disk('public')->exists($file->file_path)) {
            abort(404, _trans('common.File not found.'));
        }

        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }

    /**
     * Delete an attached project file.
     */
    public function deleteFile(ProjectFile $file): RedirectResponse
    {
        $this->projectService->deleteFile($file);

        return back()->with('success', _trans('common.File deleted successfully.'));
    }
}
