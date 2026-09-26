<?php

namespace App\Console\Commands;

use App\Services\Attendance\AttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAbsentAttendanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absent {--date= : Specific date in Y-m-d format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark absent, weekend, or holiday attendance records for employees who did not check in';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceService $attendanceService): int
    {
        $dateStr = $this->option('date');
        $date = $dateStr ? Carbon::parse($dateStr) : Carbon::today();

        $this->info("Processing automated attendance marking for date: {$date->toDateString()}...");

        $count = $attendanceService->markDailyAbsentRecords($date);

        $this->info("Successfully generated {$count} attendance records.");

        return Command::SUCCESS;
    }
}
