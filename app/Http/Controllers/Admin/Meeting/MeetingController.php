<?php

namespace App\Http\Controllers\Admin\Meeting;

use App\Enums\MeetingAttendeeResponseEnum;
use App\Enums\MeetingStatusEnum;
use App\Enums\MeetingTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Meeting\RsvpMeetingRequest;
use App\Http\Requests\Meeting\StoreMeetingMinuteRequest;
use App\Http\Requests\Meeting\StoreMeetingRequest;
use App\Http\Requests\Meeting\UpdateMeetingRequest;
use App\Models\Client;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use App\Services\Meeting\MeetingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function __construct(
        protected MeetingService $meetingService
    ) {}

    /**
     * Display a listing of meetings (Calendar / List).
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'search',
            'status',
            'type',
            'project_id',
            'organizer_id',
            'start_date',
            'end_date',
            'my_meetings',
        ]);

        $viewMode = $request->input('view', 'calendar');
        $stats = $this->meetingService->getStats($filters);
        $projects = Project::orderBy('name')->get();
        $employees = User::with('employeeDetail.designation')->active()->get();
        $clients = Client::active()->orderBy('company_name')->get();
        $types = MeetingTypeEnum::cases();
        $statuses = MeetingStatusEnum::cases();

        if ($viewMode === 'list') {
            $meetings = $this->meetingService->getPaginatedMeetings($filters, 15);
            $events = [];
        } else {
            $meetings = null;
            $events = $this->meetingService->getCalendarEvents($filters);
        }

        $title = _trans('common.Meetings & Schedules');

        return view('admin.meetings.index', compact(
            'title',
            'viewMode',
            'meetings',
            'events',
            'stats',
            'filters',
            'projects',
            'employees',
            'clients',
            'types',
            'statuses'
        ));
    }

    /**
     * Get meetings as JSON events for FullCalendar.
     */
    public function events(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'status',
            'type',
            'project_id',
            'organizer_id',
            'start_date',
            'end_date',
            'my_meetings',
        ]);

        if ($request->filled('start')) {
            $filters['start_date'] = substr($request->input('start'), 0, 10);
        }
        if ($request->filled('end')) {
            $filters['end_date'] = substr($request->input('end'), 0, 10);
        }

        $events = $this->meetingService->getCalendarEvents($filters);

        return response()->json($events);
    }

    /**
     * Show the form for creating a new meeting.
     */
    public function create(Request $request): View
    {
        $title = _trans('common.Schedule New Meeting');
        $projects = Project::orderBy('name')->get();
        $employees = User::with('employeeDetail.designation')->active()->get();
        $clients = Client::active()->orderBy('company_name')->get();
        $types = MeetingTypeEnum::cases();
        $statuses = MeetingStatusEnum::cases();

        $defaultDate = $request->input('date', now()->format('Y-m-d'));
        $defaultStartTime = $request->input('start_time', '10:00');
        $defaultEndTime = $request->input('end_time', '11:00');
        $defaultProjectId = $request->input('project_id');

        return view('admin.meetings.create', compact(
            'title',
            'projects',
            'employees',
            'clients',
            'types',
            'statuses',
            'defaultDate',
            'defaultStartTime',
            'defaultEndTime',
            'defaultProjectId'
        ));
    }

    /**
     * Store a newly created meeting.
     */
    public function store(StoreMeetingRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        // Check conflicts if not explicitly ignored
        if (! $request->boolean('ignore_conflicts')) {
            $conflictCheck = $this->meetingService->checkConflicts($validated);
            if ($conflictCheck['has_conflict']) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'has_conflict' => true,
                        'conflicts' => $conflictCheck['conflicts'],
                        'warnings' => $conflictCheck['warnings'],
                    ], 422);
                }

                return back()
                    ->withInput()
                    ->with('conflict_warnings', $conflictCheck['conflicts'])
                    ->withErrors(['location' => $conflictCheck['conflicts'][0] ?? _trans('common.Scheduling conflict detected.')]);
            }
        }

        $meeting = $this->meetingService->create($validated, Auth::id());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Meeting scheduled successfully.'),
                'data' => $meeting,
                'redirect' => route('meetings.show', $meeting),
            ]);
        }

        return redirect()->route('meetings.show', $meeting)->with('success', _trans('common.Meeting scheduled successfully.'));
    }

    /**
     * Display the specified meeting.
     */
    public function show(Meeting $meeting): View
    {
        $meeting->load([
            'organizer.employeeDetail.designation',
            'project',
            'attendees.attendee',
            'employeeAttendees.employeeDetail.designation',
            'clientAttendees',
            'minutes.recorder.employeeDetail.designation',
        ]);

        $title = $meeting->title;
        $currentUserRsvp = $meeting->attendees
            ->where('attendee_type', User::class)
            ->where('attendee_id', Auth::id())
            ->first();

        $rsvpResponses = MeetingAttendeeResponseEnum::cases();

        return view('admin.meetings.show', compact(
            'title',
            'meeting',
            'currentUserRsvp',
            'rsvpResponses'
        ));
    }

    /**
     * Show the form for editing the specified meeting.
     */
    public function edit(Meeting $meeting): View
    {
        $meeting->load(['employeeAttendees', 'clientAttendees']);

        $title = _trans('common.Edit Meeting') . ' — ' . $meeting->title;
        $projects = Project::orderBy('name')->get();
        $employees = User::with('employeeDetail.designation')->active()->get();
        $clients = Client::active()->orderBy('company_name')->get();
        $types = MeetingTypeEnum::cases();
        $statuses = MeetingStatusEnum::cases();

        return view('admin.meetings.edit', compact(
            'title',
            'meeting',
            'projects',
            'employees',
            'clients',
            'types',
            'statuses'
        ));
    }

    /**
     * Update the specified meeting.
     */
    public function update(UpdateMeetingRequest $request, Meeting $meeting): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        // Check conflicts if not ignored
        if (! $request->boolean('ignore_conflicts')) {
            $conflictCheck = $this->meetingService->checkConflicts($validated, $meeting->id);
            if ($conflictCheck['has_conflict']) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'has_conflict' => true,
                        'conflicts' => $conflictCheck['conflicts'],
                        'warnings' => $conflictCheck['warnings'],
                    ], 422);
                }

                return back()
                    ->withInput()
                    ->with('conflict_warnings', $conflictCheck['conflicts'])
                    ->withErrors(['location' => $conflictCheck['conflicts'][0] ?? _trans('common.Scheduling conflict detected.')]);
            }
        }

        $updated = $this->meetingService->update($meeting, $validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Meeting updated successfully.'),
                'data' => $updated,
                'redirect' => route('meetings.show', $updated),
            ]);
        }

        return redirect()->route('meetings.show', $meeting)->with('success', _trans('common.Meeting updated successfully.'));
    }

    /**
     * Remove the specified meeting (Soft Delete).
     */
    public function destroy(Request $request, Meeting $meeting): RedirectResponse|JsonResponse
    {
        $this->meetingService->delete($meeting);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Meeting cancelled successfully.'),
            ]);
        }

        return redirect()->route('meetings.index')->with('success', _trans('common.Meeting cancelled successfully.'));
    }

    /**
     * RSVP to meeting invitation.
     */
    public function rsvp(RsvpMeetingRequest $request, Meeting $meeting): RedirectResponse|JsonResponse
    {
        $attendee = $this->meetingService->updateRsvp(
            $meeting,
            Auth::id(),
            User::class,
            $request->response,
            $request->notes
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.RSVP response updated.'),
                'attendee' => $attendee,
                'response_label' => $attendee->response->label(),
                'response_badge' => $attendee->response->badgeClass(),
            ]);
        }

        return back()->with('success', _trans('common.RSVP response updated.'));
    }

    /**
     * Save meeting minutes.
     */
    public function saveMinutes(StoreMeetingMinuteRequest $request, Meeting $meeting): RedirectResponse|JsonResponse
    {
        $minute = $this->meetingService->saveMinutes($meeting, $request->validated(), Auth::id());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Meeting minutes recorded successfully.'),
                'data' => $minute,
            ]);
        }

        return back()->with('success', _trans('common.Meeting minutes recorded successfully.'));
    }

    /**
     * Check for scheduling conflicts via AJAX.
     */
    public function checkConflict(Request $request): JsonResponse
    {
        $data = $request->only(['date', 'start_time', 'end_time', 'location', 'organizer_id']);
        $ignoreId = $request->input('ignore_id');

        $result = $this->meetingService->checkConflicts($data, $ignoreId ? (int) $ignoreId : null);

        return response()->json($result);
    }
}
