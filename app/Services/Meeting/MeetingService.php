<?php

namespace App\Services\Meeting;

use App\Enums\MeetingAttendeeResponseEnum;
use App\Enums\MeetingStatusEnum;
use App\Enums\MeetingTypeEnum;
use App\Models\Client;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\MeetingMinute;
use App\Models\User;
use App\Notifications\MeetingInvitationNotification;
use App\Notifications\MeetingReminderNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MeetingService
{
    /**
     * Get meetings formatted as FullCalendar event objects.
     */
    public function getCalendarEvents(array $filters = []): array
    {
        $query = $this->buildFilteredQuery($filters);

        $meetings = $query->get();

        return $meetings->map(function (Meeting $meeting) {
            $startDate = $meeting->date ? $meeting->date->format('Y-m-d') : null;
            $startTime = $meeting->start_time ? substr((string) $meeting->start_time, 0, 5) : '09:00';
            $endTime = $meeting->end_time ? substr((string) $meeting->end_time, 0, 5) : '10:00';

            $start = "{$startDate}T{$startTime}:00";
            $end = "{$startDate}T{$endTime}:00";

            $bgColor = match ($meeting->status) {
                MeetingStatusEnum::COMPLETED => '#10b981', // green
                MeetingStatusEnum::CANCELLED => '#ef4444', // red
                default => $meeting->type === MeetingTypeEnum::ONLINE ? '#0ea5e9' : ($meeting->type === MeetingTypeEnum::HYBRID ? '#8b5cf6' : '#4f46e5'),
            };

            return [
                'id' => (string) $meeting->id,
                'title' => $meeting->title,
                'start' => $start,
                'end' => $end,
                'backgroundColor' => $bgColor,
                'borderColor' => $bgColor,
                'textColor' => '#ffffff',
                'url' => route('meetings.show', $meeting->id),
                'extendedProps' => [
                    'meetingId' => $meeting->id,
                    'type' => $meeting->type->label(),
                    'typeIcon' => $meeting->type->icon(),
                    'status' => $meeting->status->label(),
                    'statusClass' => $meeting->status->badgeClass(),
                    'location' => $meeting->location ?: ($meeting->meeting_link ?: _trans('common.N/A')),
                    'organizer' => $meeting->organizer?->name ?? _trans('common.N/A'),
                    'organizerAvatar' => $meeting->organizer?->avatar_url,
                    'timeRange' => $meeting->formatted_time_range,
                    'attendeesCount' => $meeting->attendees->count(),
                    'project' => $meeting->project?->name,
                ],
            ];
        })->toArray();
    }

    /**
     * Get paginated meetings for list view.
     */
    public function getPaginatedMeetings(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildFilteredQuery($filters);

        return $query->latest('date')
            ->latest('start_time')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get meeting overview stats.
     */
    public function getStats(array $filters = []): array
    {
        $baseQuery = Meeting::query();

        if (! empty($filters['project_id'])) {
            $baseQuery->project($filters['project_id']);
        }

        if (! empty($filters['organizer_id'])) {
            $baseQuery->organizer($filters['organizer_id']);
        }

        $userId = auth()->id();

        return [
            'total' => (clone $baseQuery)->count(),
            'scheduled' => (clone $baseQuery)->where('status', MeetingStatusEnum::SCHEDULED->value)->count(),
            'completed' => (clone $baseQuery)->where('status', MeetingStatusEnum::COMPLETED->value)->count(),
            'cancelled' => (clone $baseQuery)->where('status', MeetingStatusEnum::CANCELLED->value)->count(),
            'today' => (clone $baseQuery)->where('date', today())->where('status', '!=', MeetingStatusEnum::CANCELLED->value)->count(),
            'my_meetings' => $userId ? (clone $baseQuery)->forUser($userId)->count() : 0,
        ];
    }

    /**
     * Check for room or organizer scheduling conflicts.
     */
    public function checkConflicts(array $data, ?int $ignoreMeetingId = null): array
    {
        $conflicts = [];
        $warnings = [];

        $date = ! empty($data['date']) ? Carbon::parse($data['date'])->format('Y-m-d') : null;
        $startTime = ! empty($data['start_time']) ? (strlen((string) $data['start_time']) === 5 ? "{$data['start_time']}:00" : (string) $data['start_time']) : null;
        $endTime = ! empty($data['end_time']) ? (strlen((string) $data['end_time']) === 5 ? "{$data['end_time']}:00" : (string) $data['end_time']) : null;
        $location = trim($data['location'] ?? '');
        $organizerId = ! empty($data['organizer_id']) ? (int) $data['organizer_id'] : null;

        if (! $date || ! $startTime || ! $endTime) {
            return ['has_conflict' => false, 'conflicts' => [], 'warnings' => []];
        }

        // 1. Room / Location Conflict
        if (! empty($location)) {
            $roomConflict = Meeting::whereDate('date', $date)
                ->where('status', '!=', MeetingStatusEnum::CANCELLED->value)
                ->where('location', $location)
                ->when($ignoreMeetingId, fn ($q) => $q->where('id', '!=', $ignoreMeetingId))
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->with('organizer')
                ->first();

            if ($roomConflict) {
                $msg = _trans('common.Room conflict: ":room" is already booked by :organizer from :time for ":title".', [
                    'room' => $location,
                    'organizer' => $roomConflict->organizer?->name ?? 'another meeting',
                    'time' => $roomConflict->formatted_time_range,
                    'title' => $roomConflict->title,
                ]);
                $conflicts[] = $msg;
                $warnings['location'] = $msg;
            }
        }

        // 2. Organizer Conflict
        if (! empty($organizerId)) {
            $organizerConflict = Meeting::whereDate('date', $date)
                ->where('status', '!=', MeetingStatusEnum::CANCELLED->value)
                ->where('organizer_id', $organizerId)
                ->when($ignoreMeetingId, fn ($q) => $q->where('id', '!=', $ignoreMeetingId))
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->first();

            if ($organizerConflict) {
                $msg = _trans('common.Organizer schedule conflict: Organizer already has meeting ":title" from :time.', [
                    'title' => $organizerConflict->title,
                    'time' => $organizerConflict->formatted_time_range,
                ]);
                $conflicts[] = $msg;
                $warnings['organizer'] = $msg;
            }
        }

        return [
            'has_conflict' => ! empty($conflicts),
            'conflicts' => $conflicts,
            'warnings' => $warnings,
        ];
    }

    /**
     * Create a new meeting.
     */
    public function create(array $data, int $organizerId): Meeting
    {
        return DB::transaction(function () use ($data, $organizerId) {
            $employeeAttendees = $data['employee_attendees'] ?? [];
            $clientAttendees = $data['client_attendees'] ?? [];
            unset($data['employee_attendees'], $data['client_attendees'], $data['ignore_conflicts']);

            $data['organizer_id'] = $data['organizer_id'] ?? $organizerId;
            $data['status'] = $data['status'] ?? MeetingStatusEnum::SCHEDULED->value;

            $meeting = Meeting::create($data);

            // Attach employee attendees
            $this->syncAttendees($meeting, $employeeAttendees, $clientAttendees);

            // Send notification to employee attendees
            $this->notifyAttendees($meeting, 'invited');

            return $meeting->fresh(['organizer', 'project', 'attendees.attendee', 'employeeAttendees', 'clientAttendees']);
        });
    }

    /**
     * Update an existing meeting.
     */
    public function update(Meeting $meeting, array $data): Meeting
    {
        return DB::transaction(function () use ($meeting, $data) {
            $hasEmployeeAttendees = array_key_exists('employee_attendees', $data);
            $hasClientAttendees = array_key_exists('client_attendees', $data);

            $employeeAttendees = $data['employee_attendees'] ?? [];
            $clientAttendees = $data['client_attendees'] ?? [];
            unset($data['employee_attendees'], $data['client_attendees'], $data['ignore_conflicts']);

            $meeting->update($data);

            if ($hasEmployeeAttendees || $hasClientAttendees) {
                $this->syncAttendees($meeting, $employeeAttendees, $clientAttendees);
            }

            // Notify attendees of changes
            $this->notifyAttendees($meeting, 'updated');

            return $meeting->fresh(['organizer', 'project', 'attendees.attendee', 'employeeAttendees', 'clientAttendees', 'minutes']);
        });
    }

    /**
     * Delete a meeting (Soft Delete).
     */
    public function delete(Meeting $meeting): bool
    {
        return DB::transaction(function () use ($meeting) {
            $this->notifyAttendees($meeting, 'cancelled');

            return (bool) $meeting->delete();
        });
    }

    /**
     * Update attendee RSVP status.
     */
    public function updateRsvp(
        Meeting $meeting,
        int $attendeeId,
        string $attendeeType,
        string|MeetingAttendeeResponseEnum $response,
        ?string $notes = null
    ): MeetingAttendee {
        $responseVal = $response instanceof MeetingAttendeeResponseEnum ? $response->value : $response;

        $attendee = MeetingAttendee::firstOrCreate(
            [
                'meeting_id' => $meeting->id,
                'attendee_type' => $attendeeType,
                'attendee_id' => $attendeeId,
            ],
            [
                'response' => $responseVal,
                'notes' => $notes,
            ]
        );

        $attendee->update([
            'response' => $responseVal,
            'notes' => $notes,
        ]);

        return $attendee;
    }

    /**
     * Save or update meeting minutes.
     */
    public function saveMinutes(Meeting $meeting, array $data, int $recordedBy): MeetingMinute
    {
        return DB::transaction(function () use ($meeting, $data, $recordedBy) {
            $markCompleted = $data['mark_completed'] ?? true;
            unset($data['mark_completed']);

            $data['recorded_by'] = $recordedBy;

            $minute = MeetingMinute::updateOrCreate(
                ['meeting_id' => $meeting->id],
                $data
            );

            if ($markCompleted) {
                $meeting->update(['status' => MeetingStatusEnum::COMPLETED->value]);
            }

            return $minute;
        });
    }

    /**
     * Send reminders for upcoming meetings in ~30 minutes.
     */
    public function sendReminders(): int
    {
        $now = Carbon::now();
        $targetFrom = $now->copy()->addMinutes(20)->format('H:i:s');
        $targetTo = $now->copy()->addMinutes(45)->format('H:i:s');

        $upcomingMeetings = Meeting::where('date', today())
            ->where('status', MeetingStatusEnum::SCHEDULED->value)
            ->where('reminder_sent', false)
            ->whereBetween('start_time', [$targetFrom, $targetTo])
            ->with(['organizer', 'employeeAttendees'])
            ->get();

        $sentCount = 0;

        foreach ($upcomingMeetings as $meeting) {
            $recipients = collect();

            if ($meeting->organizer) {
                $recipients->push($meeting->organizer);
            }

            foreach ($meeting->employeeAttendees as $employee) {
                if ($meeting->organizer_id !== $employee->id) {
                    $recipients->push($employee);
                }
            }

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients->unique('id'), new MeetingReminderNotification($meeting));
            }

            $meeting->update(['reminder_sent' => true]);
            $sentCount++;
        }

        return $sentCount;
    }

    /* -------------------------------------------------------------------------- */
    /*                               Helper Methods                               */
    /* -------------------------------------------------------------------------- */

    protected function syncAttendees(Meeting $meeting, array $employeeIds = [], array $clientIds = []): void
    {
        // Keep existing responses if attendee is already in list
        $existingAttendees = $meeting->attendees->keyBy(fn ($a) => "{$a->attendee_type}_{$a->attendee_id}");

        // Clear existing attendees
        $meeting->attendees()->delete();

        // Add employees
        foreach (array_unique($employeeIds) as $empId) {
            if (empty($empId)) continue;
            $key = User::class . "_{$empId}";
            $prev = $existingAttendees->get($key);

            MeetingAttendee::create([
                'meeting_id' => $meeting->id,
                'attendee_type' => User::class,
                'attendee_id' => $empId,
                'response' => $prev ? $prev->response->value : MeetingAttendeeResponseEnum::PENDING->value,
                'notes' => $prev?->notes,
            ]);
        }

        // Add clients
        foreach (array_unique($clientIds) as $clientId) {
            if (empty($clientId)) continue;
            $key = Client::class . "_{$clientId}";
            $prev = $existingAttendees->get($key);

            MeetingAttendee::create([
                'meeting_id' => $meeting->id,
                'attendee_type' => Client::class,
                'attendee_id' => $clientId,
                'response' => $prev ? $prev->response->value : MeetingAttendeeResponseEnum::PENDING->value,
                'notes' => $prev?->notes,
            ]);
        }
    }

    protected function notifyAttendees(Meeting $meeting, string $action): void
    {
        $meeting->loadMissing(['organizer', 'employeeAttendees']);
        $recipients = $meeting->employeeAttendees->reject(fn ($emp) => $emp->id === auth()->id());

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new MeetingInvitationNotification($meeting, $action));
        }
    }

    protected function buildFilteredQuery(array $filters = []): Builder
    {
        $query = Meeting::with([
            'organizer.employeeDetail.designation',
            'project',
            'attendees.attendee',
            'employeeAttendees.employeeDetail.designation',
            'clientAttendees',
            'minutes',
        ]);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->type($filters['type']);
        }

        if (! empty($filters['project_id'])) {
            $query->project($filters['project_id']);
        }

        if (! empty($filters['organizer_id'])) {
            $query->organizer($filters['organizer_id']);
        }

        if (! empty($filters['start_date']) || ! empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'] ?? null, $filters['end_date'] ?? null);
        }

        if (! empty($filters['my_meetings']) && auth()->check()) {
            $query->forUser(auth()->id());
        }

        return $query;
    }
}
