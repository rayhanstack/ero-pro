<?php

namespace Tests\Feature;

use App\Enums\MeetingAttendeeResponseEnum;
use App\Enums\MeetingStatusEnum;
use App\Enums\MeetingTypeEnum;
use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectStatusEnum;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\MeetingMinute;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MeetingInvitationNotification;
use App\Notifications\MeetingReminderNotification;
use App\Services\Meeting\MeetingService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MeetingManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee1;
    protected User $employee2;
    protected Client $client;
    protected Project $project;
    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->employee1 = User::factory()->create();
        $this->employee1->assignRole('Employee');

        $this->employee2 = User::factory()->create();
        $this->employee2->assignRole('Employee');

        $this->currency = Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'rate' => 1.000000,
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->client = Client::create([
            'code' => 'CLT-0001',
            'company_name' => 'Nexus Global Inc',
            'contact_name' => 'John Doe',
            'email' => 'john@nexus.test',
            'phone' => '+1 555-0101',
            'status' => 'active',
            'currency_id' => $this->currency->id,
        ]);

        $this->project = Project::create([
            'code' => 'PRJ-0001',
            'name' => 'ERP Meeting Suite',
            'client_id' => $this->client->id,
            'description' => 'Meeting system implementation',
            'start_date' => '2026-01-01',
            'deadline' => '2026-12-31',
            'budget' => 30000.00,
            'currency_id' => $this->currency->id,
            'priority' => ProjectPriorityEnum::HIGH,
            'status' => ProjectStatusEnum::ACTIVE,
            'progress' => 0,
            'manager_id' => $this->admin->id,
        ]);
    }

    public function test_authorized_user_can_view_meetings_calendar_and_list(): void
    {
        $meeting = Meeting::create([
            'title' => 'Weekly Team Standup',
            'agenda' => 'Review weekly blocker items',
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:30',
            'end_time' => '10:30',
            'location' => 'Room 101',
            'type' => MeetingTypeEnum::IN_PERSON,
            'organizer_id' => $this->admin->id,
            'status' => MeetingStatusEnum::SCHEDULED,
        ]);

        // Calendar view
        $resCalendar = $this->actingAs($this->admin)->get(route('meetings.index'));
        $resCalendar->assertStatus(200);
        $resCalendar->assertSee('meetingCalendar');

        // List view
        $resList = $this->actingAs($this->admin)->get(route('meetings.index', ['view' => 'list']));
        $resList->assertStatus(200);
        $resList->assertSee('Weekly Team Standup');
        $resList->assertSee('Room 101');
    }

    public function test_events_endpoint_returns_fullcalendar_formatted_json(): void
    {
        Meeting::create([
            'title' => 'Calendar Event Sync',
            'date' => '2026-06-15',
            'start_time' => '11:00',
            'end_time' => '12:00',
            'location' => 'Room 202',
            'type' => MeetingTypeEnum::HYBRID,
            'organizer_id' => $this->admin->id,
            'status' => MeetingStatusEnum::SCHEDULED,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('meetings.events', [
            'start' => '2026-06-01',
            'end' => '2026-06-30',
        ]));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => 'Calendar Event Sync',
            'start' => '2026-06-15T11:00:00',
            'end' => '2026-06-15T12:00:00',
        ]);
    }

    public function test_authorized_user_can_schedule_meeting_with_attendees_and_notifications_are_sent(): void
    {
        Notification::fake();

        $data = [
            'title' => 'Sprint Planning Session',
            'agenda' => 'Sprint goals, backlog grooming, velocity planning.',
            'date' => '2026-04-10',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'location' => 'Main Conference Hall',
            'type' => MeetingTypeEnum::IN_PERSON->value,
            'organizer_id' => $this->admin->id,
            'project_id' => $this->project->id,
            'employee_attendees' => [$this->employee1->id, $this->employee2->id],
            'client_attendees' => [$this->client->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('meetings.store'), $data);

        $meeting = Meeting::where('title', 'Sprint Planning Session')->first();
        $this->assertNotNull($meeting);
        $response->assertRedirect(route('meetings.show', $meeting));

        $this->assertDatabaseHas('meetings', [
            'title' => 'Sprint Planning Session',
            'location' => 'Main Conference Hall',
            'project_id' => $this->project->id,
        ]);

        $this->assertDatabaseHas('meeting_attendees', [
            'meeting_id' => $meeting->id,
            'attendee_type' => User::class,
            'attendee_id' => $this->employee1->id,
            'response' => 'pending',
        ]);

        $this->assertDatabaseHas('meeting_attendees', [
            'meeting_id' => $meeting->id,
            'attendee_type' => Client::class,
            'attendee_id' => $this->client->id,
            'response' => 'pending',
        ]);

        Notification::assertSentTo([$this->employee1, $this->employee2], MeetingInvitationNotification::class);
    }

    public function test_room_and_organizer_scheduling_conflicts_are_detected(): void
    {
        // Existing meeting: 2026-05-01 from 14:00 to 15:00 at "Room A" by admin
        Meeting::create([
            'title' => 'Existing Conflicting Call',
            'date' => '2026-05-01',
            'start_time' => '14:00',
            'end_time' => '15:00',
            'location' => 'Room A',
            'type' => MeetingTypeEnum::IN_PERSON,
            'organizer_id' => $this->admin->id,
            'status' => MeetingStatusEnum::SCHEDULED,
        ]);

        $service = app(MeetingService::class);

        // 1. Check room overlap (14:30 to 15:30 in Room A)
        $conflict1 = $service->checkConflicts([
            'date' => '2026-05-01',
            'start_time' => '14:30',
            'end_time' => '15:30',
            'location' => 'Room A',
            'organizer_id' => $this->employee1->id,
        ]);

        $this->assertTrue($conflict1['has_conflict']);
        $this->assertArrayHasKey('location', $conflict1['warnings']);

        // 2. Check organizer overlap for admin (14:15 to 14:45 in another room)
        $conflict2 = $service->checkConflicts([
            'date' => '2026-05-01',
            'start_time' => '14:15',
            'end_time' => '14:45',
            'location' => 'Room B',
            'organizer_id' => $this->admin->id,
        ]);

        $this->assertTrue($conflict2['has_conflict']);
        $this->assertArrayHasKey('organizer', $conflict2['warnings']);

        // 3. No conflict on different time (16:00 to 17:00 in Room A)
        $noConflict = $service->checkConflicts([
            'date' => '2026-05-01',
            'start_time' => '16:00',
            'end_time' => '17:00',
            'location' => 'Room A',
            'organizer_id' => $this->admin->id,
        ]);

        $this->assertFalse($noConflict['has_conflict']);

        // 4. AJAX conflict endpoint
        $res = $this->actingAs($this->admin)->getJson(route('meetings.check-conflict', [
            'date' => '2026-05-01',
            'start_time' => '14:30',
            'end_time' => '15:30',
            'location' => 'Room A',
        ]));

        $res->assertStatus(200);
        $res->assertJson(['has_conflict' => true]);
    }

    public function test_attendee_can_rsvp_to_meeting(): void
    {
        $meeting = Meeting::create([
            'title' => 'RSVP Test Meeting',
            'date' => '2026-07-01',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'type' => MeetingTypeEnum::ONLINE,
            'organizer_id' => $this->admin->id,
            'status' => MeetingStatusEnum::SCHEDULED,
        ]);

        MeetingAttendee::create([
            'meeting_id' => $meeting->id,
            'attendee_type' => User::class,
            'attendee_id' => $this->employee1->id,
            'response' => MeetingAttendeeResponseEnum::PENDING,
        ]);

        // Employee1 accepts RSVP
        $response = $this->actingAs($this->employee1)->post(route('meetings.rsvp', $meeting), [
            'response' => MeetingAttendeeResponseEnum::ACCEPTED->value,
            'notes' => 'Attending remotely via Zoom.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('meeting_attendees', [
            'meeting_id' => $meeting->id,
            'attendee_type' => User::class,
            'attendee_id' => $this->employee1->id,
            'response' => 'accepted',
            'notes' => 'Attending remotely via Zoom.',
        ]);
    }

    public function test_authorized_user_can_record_meeting_minutes_and_mark_completed(): void
    {
        $meeting = Meeting::create([
            'title' => 'Quarterly Retrospective',
            'date' => '2026-08-01',
            'start_time' => '15:00',
            'end_time' => '16:00',
            'type' => MeetingTypeEnum::IN_PERSON,
            'organizer_id' => $this->admin->id,
            'status' => MeetingStatusEnum::SCHEDULED,
        ]);

        $minutesData = [
            'discussion' => 'Team reflected on Sprint deliverables, velocity was up 15%.',
            'decisions' => '1. Adopt daily async standups on Fridays. 2. Implement strict code review SLA.',
            'action_items' => [
                ['task' => 'Setup CI test matrix', 'assignee' => 'Dev Lead', 'due_date' => '2026-08-10'],
                ['task' => 'Update documentation', 'assignee' => 'Tech Writer', 'due_date' => '2026-08-15'],
            ],
            'mark_completed' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('meetings.minutes.store', $meeting), $minutesData);

        $response->assertRedirect();
        $this->assertDatabaseHas('meeting_minutes', [
            'meeting_id' => $meeting->id,
            'recorded_by' => $this->admin->id,
            'discussion' => 'Team reflected on Sprint deliverables, velocity was up 15%.',
        ]);

        $meeting->refresh();
        $this->assertEquals(MeetingStatusEnum::COMPLETED, $meeting->status);
    }

    public function test_authorized_user_can_edit_and_delete_meeting(): void
    {
        $meeting = Meeting::create([
            'title' => 'Initial Title',
            'date' => '2026-09-01',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'type' => MeetingTypeEnum::IN_PERSON,
            'organizer_id' => $this->admin->id,
            'status' => MeetingStatusEnum::SCHEDULED,
        ]);

        // Edit page
        $resEdit = $this->actingAs($this->admin)->get(route('meetings.edit', $meeting));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Initial Title');

        // Update
        $resUpdate = $this->actingAs($this->admin)->put(route('meetings.update', $meeting), [
            'title' => 'Updated Meeting Title',
            'date' => '2026-09-02',
            'start_time' => '11:00',
            'end_time' => '12:00',
            'type' => MeetingTypeEnum::ONLINE->value,
            'status' => MeetingStatusEnum::SCHEDULED->value,
            'meeting_link' => 'https://meet.google.com/xyz',
            'organizer_id' => $this->admin->id,
        ]);

        $resUpdate->assertRedirect(route('meetings.show', $meeting));
        $meeting->refresh();
        $this->assertEquals('Updated Meeting Title', $meeting->title);
        $this->assertEquals(MeetingTypeEnum::ONLINE, $meeting->type);

        // Delete (Soft delete)
        $resDelete = $this->actingAs($this->admin)->delete(route('meetings.destroy', $meeting));
        $resDelete->assertRedirect(route('meetings.index'));
        $this->assertSoftDeleted('meetings', ['id' => $meeting->id]);
    }

    public function test_scheduled_reminder_command_sends_notifications_to_attendees(): void
    {
        Notification::fake();

        // Meeting starting in 30 minutes from now
        $meeting = Meeting::create([
            'title' => 'Immediate Upcoming Standup',
            'date' => today()->format('Y-m-d'),
            'start_time' => now()->addMinutes(30)->format('H:i:s'),
            'end_time' => now()->addMinutes(60)->format('H:i:s'),
            'type' => MeetingTypeEnum::ONLINE,
            'organizer_id' => $this->admin->id,
            'status' => MeetingStatusEnum::SCHEDULED,
            'reminder_sent' => false,
        ]);

        MeetingAttendee::create([
            'meeting_id' => $meeting->id,
            'attendee_type' => User::class,
            'attendee_id' => $this->employee1->id,
            'response' => MeetingAttendeeResponseEnum::ACCEPTED,
        ]);

        $this->artisan('meetings:send-reminders')->assertExitCode(0);

        Notification::assertSentTo([$this->admin, $this->employee1], MeetingReminderNotification::class, function ($notification) use ($meeting) {
            return $notification->meeting->id === $meeting->id;
        });

        $meeting->refresh();
        $this->assertTrue($meeting->reminder_sent);
    }

    public function test_unauthorized_user_cannot_access_meetings(): void
    {
        $unauthorized = User::factory()->create();

        $response = $this->actingAs($unauthorized)->get(route('meetings.index'));
        $response->assertStatus(403);
    }
}
