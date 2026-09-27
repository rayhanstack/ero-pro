<?php

namespace Database\Seeders;

use App\Enums\EmployeeStatusEnum;
use App\Enums\LeaveRequestStatusEnum;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\Leave\LeaveService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(LeaveService $leaveService): void
    {
        $currentYear = (int) date('Y');

        // 1. Allocate leave balances for all active employees for current year and previous year
        $leaveService->allocateYearly($currentYear - 1);
        $leaveService->allocateYearly($currentYear);

        // 2. Fetch seed users and leave types
        $employees = User::whereHas('employeeDetail', function ($q) {
            $q->where('status', EmployeeStatusEnum::ACTIVE);
        })->get();

        $admin = User::role('Super Admin')->first() ?? $employees->first();
        $hr = User::role('HR')->first() ?? $admin;

        $casualType = LeaveType::where('code', 'CL')->first();
        $sickType = LeaveType::where('code', 'SL')->first();
        $annualType = LeaveType::where('code', 'AL')->first();

        if ($employees->isEmpty() || ! $casualType || ! $sickType || ! $annualType) {
            return;
        }

        // 3. Create sample leave requests for the first few employees
        foreach ($employees->take(8) as $index => $emp) {
            // Employee 1 & 2: Approved Casual Leave last week
            if ($index < 2) {
                $fromDate = Carbon::now()->subWeeks(2)->startOfWeek(); // Monday
                $toDate = $fromDate->copy()->addDays(1); // Tuesday
                $days = $leaveService->calculateDays($emp->id, $fromDate, $toDate);

                if ($days > 0) {
                    $req = LeaveRequest::create([
                        'employee_id' => $emp->id,
                        'leave_type_id' => $casualType->id,
                        'from_date' => $fromDate->format('Y-m-d'),
                        'to_date' => $toDate->format('Y-m-d'),
                        'days' => $days,
                        'half_day' => false,
                        'reason' => 'Family occasion and personal work',
                        'status' => LeaveRequestStatusEnum::PENDING,
                    ]);

                    $leaveService->approve($req, $hr->id, 'Approved as requested. Enjoy your time off!');
                }
            }

            // Employee 3 & 4: Approved Sick Leave
            elseif ($index < 4) {
                $fromDate = Carbon::now()->subDays(5);
                $toDate = $fromDate->copy();
                $days = $leaveService->calculateDays($emp->id, $fromDate, $toDate);

                if ($days > 0) {
                    $req = LeaveRequest::create([
                        'employee_id' => $emp->id,
                        'leave_type_id' => $sickType->id,
                        'from_date' => $fromDate->format('Y-m-d'),
                        'to_date' => $toDate->format('Y-m-d'),
                        'days' => $days,
                        'half_day' => false,
                        'reason' => 'Severe fever and doctor appointment',
                        'status' => LeaveRequestStatusEnum::PENDING,
                    ]);

                    $leaveService->approve($req, $hr->id, 'Approved. Get well soon!');
                }
            }

            // Employee 5: Pending Annual Leave request next week
            elseif ($index === 4) {
                $fromDate = Carbon::now()->addWeeks(1)->startOfWeek();
                $toDate = $fromDate->copy()->addDays(2);
                $days = $leaveService->calculateDays($emp->id, $fromDate, $toDate);

                if ($days > 0) {
                    LeaveRequest::create([
                        'employee_id' => $emp->id,
                        'leave_type_id' => $annualType->id,
                        'from_date' => $fromDate->format('Y-m-d'),
                        'to_date' => $toDate->format('Y-m-d'),
                        'days' => $days,
                        'half_day' => false,
                        'reason' => 'Annual family vacation trip to cox bazar',
                        'status' => LeaveRequestStatusEnum::PENDING,
                    ]);
                }
            }

            // Employee 6: Pending Half-Day Leave tomorrow
            elseif ($index === 5) {
                $fromDate = Carbon::now()->addDays(2);
                $days = $leaveService->calculateDays($emp->id, $fromDate, $fromDate, true);

                if ($days > 0) {
                    LeaveRequest::create([
                        'employee_id' => $emp->id,
                        'leave_type_id' => $casualType->id,
                        'from_date' => $fromDate->format('Y-m-d'),
                        'to_date' => $fromDate->format('Y-m-d'),
                        'days' => $days,
                        'half_day' => true,
                        'half_day_type' => 'first_half',
                        'reason' => 'Morning bank work and personal errands',
                        'status' => LeaveRequestStatusEnum::PENDING,
                    ]);
                }
            }

            // Employee 7: Rejected leave request
            elseif ($index === 6) {
                $fromDate = Carbon::now()->subMonth()->startOfWeek();
                $toDate = $fromDate->copy()->addDays(3);
                $days = $leaveService->calculateDays($emp->id, $fromDate, $toDate);

                if ($days > 0) {
                    $req = LeaveRequest::create([
                        'employee_id' => $emp->id,
                        'leave_type_id' => $annualType->id,
                        'from_date' => $fromDate->format('Y-m-d'),
                        'to_date' => $toDate->format('Y-m-d'),
                        'days' => $days,
                        'half_day' => false,
                        'reason' => 'Extended leave during major project sprint release',
                        'status' => LeaveRequestStatusEnum::PENDING,
                    ]);

                    $leaveService->reject($req, $hr->id, 'Cannot approve during sprint launch window. Please reschedule.');
                }
            }
        }
    }
}
