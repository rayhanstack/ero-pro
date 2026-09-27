<?php

namespace Database\Seeders;

use App\Enums\MeetingAttendeeResponseEnum;
use App\Enums\MeetingStatusEnum;
use App\Enums\MeetingTypeEnum;
use App\Models\Client;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\MeetingMinute;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class MeetingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $projects = Project::all();
        $clients = Client::all();

        if ($users->isEmpty()) {
            return;
        }

        $organizer1 = $users->firstWhere('email', 'admin@erp.test') ?? $users->first();
        $organizer2 = $users->skip(1)->first() ?? $organizer1;

        $project1 = $projects->first();
        $project2 = $projects->skip(1)->first();
        $client1 = $clients->first();

        $meetingsData = [
            [
                'title' => 'Sprint 14 Architecture & Security Review',
                'agenda' => "1. Review attendance regularization workflow schema\n2. Discuss biometric API webhook payload security\n3. Assign DomPDF payslip rendering tasks\n4. Establish test coverage targets for Sprint 14",
                'date' => now()->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '11:00',
                'location' => 'Boardroom 101',
                'meeting_link' => null,
                'type' => MeetingTypeEnum::IN_PERSON,
                'organizer_id' => $organizer1->id,
                'project_id' => $project1?->id,
                'status' => MeetingStatusEnum::SCHEDULED,
                'employees' => [$users[1] ?? $organizer1, $users[2] ?? $organizer1, $users[3] ?? $organizer1],
                'clients' => [],
                'minutes' => null,
            ],
            [
                'title' => 'Client Design Walkthrough & Catalog Scope',
                'agenda' => "Walk the client through the high-fidelity Figma components, product facet search filters, and 3DS payment gateway checkout flow.",
                'date' => now()->addDays(2)->format('Y-m-d'),
                'start_time' => '14:30',
                'end_time' => '15:30',
                'location' => null,
                'meeting_link' => 'https://meet.google.com/erp-proj-demo',
                'type' => MeetingTypeEnum::ONLINE,
                'organizer_id' => $organizer2->id,
                'project_id' => $project2?->id,
                'status' => MeetingStatusEnum::SCHEDULED,
                'employees' => [$organizer1, $users[2] ?? $organizer1],
                'clients' => $client1 ? [$client1] : [],
                'minutes' => null,
            ],
            [
                'title' => 'Quarterly HR Policy & Leave Balance Alignment',
                'agenda' => "Review annual leave carry-forward quotas, statutory public holiday calendar, and employee attendance regularization thresholds.",
                'date' => now()->subDays(3)->format('Y-m-d'),
                'start_time' => '11:00',
                'end_time' => '12:30',
                'location' => 'Executive Conference Suite',
                'meeting_link' => 'https://zoom.us/j/987654321',
                'type' => MeetingTypeEnum::HYBRID,
                'organizer_id' => $organizer1->id,
                'project_id' => null,
                'status' => MeetingStatusEnum::COMPLETED,
                'employees' => [$users[1] ?? $organizer1, $users[2] ?? $organizer1, $users[4] ?? $organizer1],
                'clients' => [],
                'minutes' => [
                    'discussion' => "The committee discussed the proposed leave carry-forward policy for 2026. HR presented the annual balance report. Overtime regularization SLA was reduced from 7 days to 3 business days for manager sign-off.",
                    'decisions' => "• Approved maximum 10 days annual leave carry-forward.\n• Approved new Biometric attendance punch window (15 mins grace period).\n• Mandatory leave application 48 hours prior for non-emergency leaves.",
                    'action_items' => [
                        ['task' => 'Update leave settings in ERP portal', 'assignee' => 'HR Manager', 'due_date' => now()->addDays(7)->format('Y-m-d')],
                        ['task' => 'Send policy broadcast email to all staff', 'assignee' => 'Admin Team', 'due_date' => now()->addDays(3)->format('Y-m-d')],
                    ],
                ],
            ],
            [
                'title' => 'Legacy Server Infrastructure Migration Sync',
                'agenda' => "Evaluating AWS vs on-premise dedicated servers for high-availability database cluster.",
                'date' => now()->subDays(5)->format('Y-m-d'),
                'start_time' => '16:00',
                'end_time' => '17:00',
                'location' => 'Tech Room B',
                'meeting_link' => null,
                'type' => MeetingTypeEnum::IN_PERSON,
                'organizer_id' => $organizer2->id,
                'project_id' => null,
                'status' => MeetingStatusEnum::CANCELLED,
                'employees' => [$organizer1],
                'clients' => [],
                'minutes' => null,
            ],
        ];

        foreach ($meetingsData as $data) {
            $meeting = Meeting::create([
                'title' => $data['title'],
                'agenda' => $data['agenda'],
                'date' => $data['date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'location' => $data['location'],
                'meeting_link' => $data['meeting_link'],
                'type' => $data['type'],
                'organizer_id' => $data['organizer_id'],
                'project_id' => $data['project_id'],
                'status' => $data['status'],
                'reminder_sent' => false,
            ]);

            // Add employee attendees with mixed RSVP responses
            foreach ($data['employees'] as $idx => $emp) {
                $response = match ($idx % 3) {
                    0 => MeetingAttendeeResponseEnum::ACCEPTED,
                    1 => MeetingAttendeeResponseEnum::PENDING,
                    2 => MeetingAttendeeResponseEnum::DECLINED,
                };

                MeetingAttendee::create([
                    'meeting_id' => $meeting->id,
                    'attendee_type' => User::class,
                    'attendee_id' => $emp->id,
                    'response' => $response,
                    'notes' => $response === MeetingAttendeeResponseEnum::DECLINED ? 'Prior client commitment.' : null,
                ]);
            }

            // Add client attendees
            foreach ($data['clients'] as $client) {
                MeetingAttendee::create([
                    'meeting_id' => $meeting->id,
                    'attendee_type' => Client::class,
                    'attendee_id' => $client->id,
                    'response' => MeetingAttendeeResponseEnum::ACCEPTED,
                    'notes' => 'Confirmed via email.',
                ]);
            }

            // Add minutes if any
            if (! empty($data['minutes'])) {
                MeetingMinute::create([
                    'meeting_id' => $meeting->id,
                    'recorded_by' => $data['organizer_id'],
                    'discussion' => $data['minutes']['discussion'],
                    'decisions' => $data['minutes']['decisions'],
                    'action_items' => $data['minutes']['action_items'],
                ]);
            }
        }
    }
}
