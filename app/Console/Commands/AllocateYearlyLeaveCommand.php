<?php

namespace App\Console\Commands;

use App\Services\Leave\LeaveService;
use Illuminate\Console\Command;

class AllocateYearlyLeaveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:allocate-yearly {--year= : The target year (defaults to current year)} {--employee= : Specific employee ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allocate annual leave balances for all active employees and apply carry-forward rules';

    /**
     * Execute the console command.
     */
    public function handle(LeaveService $leaveService): int
    {
        $year = (int) ($this->option('year') ?: date('Y'));
        $employeeId = $this->option('employee') ? (int) $this->option('employee') : null;

        $this->info("Allocating leave balances for year {$year}...");

        $count = $leaveService->allocateYearly($year, $employeeId);

        $this->info("Successfully processed and allocated {$count} leave balance records for year {$year}.");

        return self::SUCCESS;
    }
}
