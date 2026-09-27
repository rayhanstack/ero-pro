<?php

namespace App\Http\Controllers\Admin\Team;

use App\Enums\TeamStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\AddTeamMembersRequest;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use App\Services\Team\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(
        protected TeamService $teamService
    ) {}

    /**
     * Display a listing of the teams.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'lead_id']);
        $viewMode = $request->input('view', 'grid');
        $perPage = $viewMode === 'list' ? 15 : 12;

        $teams = $this->teamService->getTeams($filters, $perPage);
        $stats = $this->teamService->getStats();
        $statuses = TeamStatusEnum::cases();
        $employees = User::with('employeeDetail.designation')->active()->get();

        return view('admin.teams.index', compact(
            'teams',
            'stats',
            'filters',
            'viewMode',
            'statuses',
            'employees'
        ));
    }

    /**
     * Show the form for creating a new team.
     */
    public function create(): View
    {
        $employees = User::with(['employeeDetail.designation', 'employeeDetail.department'])->active()->get();
        $statuses = TeamStatusEnum::cases();

        return view('admin.teams.create', compact('employees', 'statuses'));
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(StoreTeamRequest $request): RedirectResponse|JsonResponse
    {
        $team = $this->teamService->create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Team created successfully.'),
                'data' => $team,
            ]);
        }

        return redirect()->route('teams.index')->with('success', _trans('common.Team created successfully.'));
    }

    /**
     * Display the specified team profile with members and projects placeholder.
     */
    public function show(Team $team): View
    {
        $team->load([
            'lead.employeeDetail.designation',
            'lead.employeeDetail.department',
            'members.employeeDetail.designation',
            'members.employeeDetail.department',
        ]);

        $existingMemberIds = $team->members->pluck('id')->toArray();
        $availableEmployees = User::with(['employeeDetail.designation', 'employeeDetail.department'])
            ->active()
            ->whereNotIn('id', $existingMemberIds)
            ->get();

        return view('admin.teams.show', compact('team', 'availableEmployees'));
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team): View
    {
        $team->load(['lead', 'members']);
        $employees = User::with(['employeeDetail.designation', 'employeeDetail.department'])->active()->get();
        $statuses = TeamStatusEnum::cases();
        $selectedMemberIds = $team->members->pluck('id')->toArray();

        return view('admin.teams.edit', compact('team', 'employees', 'statuses', 'selectedMemberIds'));
    }

    /**
     * Update the specified team in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse|JsonResponse
    {
        $this->teamService->update($team, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Team updated successfully.'),
                'data' => $team,
            ]);
        }

        return redirect()->route('teams.show', $team)->with('success', _trans('common.Team updated successfully.'));
    }

    /**
     * Remove the specified team from storage (Soft Delete).
     */
    public function destroy(Team $team): RedirectResponse|JsonResponse
    {
        $this->teamService->delete($team);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Team deleted successfully.'),
            ]);
        }

        return redirect()->route('teams.index')->with('success', _trans('common.Team deleted successfully.'));
    }

    /**
     * Restore a soft-deleted team.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->teamService->restore($id);

        return redirect()->route('teams.index')->with('success', _trans('common.Team restored successfully.'));
    }

    /**
     * Add members to a team.
     */
    public function addMembers(AddTeamMembersRequest $request, Team $team): RedirectResponse|JsonResponse
    {
        $this->teamService->addMembers($team, $request->input('member_ids'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Members added to team successfully.'),
            ]);
        }

        return back()->with('success', _trans('common.Members added to team successfully.'));
    }

    /**
     * Remove a member from a team.
     */
    public function removeMember(Team $team, User $user): RedirectResponse|JsonResponse
    {
        $this->teamService->removeMember($team, $user);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Member removed from team successfully.'),
            ]);
        }

        return back()->with('success', _trans('common.Member removed from team successfully.'));
    }
}
