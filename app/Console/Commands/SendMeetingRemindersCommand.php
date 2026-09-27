<?php

namespace App\Console\Commands;

use App\Services\Meeting\MeetingService;
use Illuminate\Console\Command;

class SendMeetingRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 30-minute upcoming meeting reminder notifications to attendees';

    /**
     * Execute the console command.
     */
    public function handle(MeetingService $meetingService): int
    {
        $this->info('Checking for upcoming meetings starting in ~30 minutes...');

        $sentCount = $meetingService->sendReminders();

        $this->info("Successfully sent reminders for {$sentCount} upcoming meeting(s).");

        return self::SUCCESS;
    }
}
