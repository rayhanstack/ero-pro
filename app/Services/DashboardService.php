<?php

namespace App\Services;

use App\Enums\AttendanceStatusEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\LeaveRequestStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\TaskStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\LeaveRequest;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Cache TTL in minutes.
     */
    public const CACHE_TTL_MINUTES = 5;

    /**
     * Get all aggregated dashboard data with 5-minute caching.
     */
    public function getDashboardData(User $user): array
    {
        $cacheKey = "dashboard_user_{$user->id}_data";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($user) {
            return [
                'hr_stats' => $this->getHrStats(),
                'project_stats' => $this->getProjectStats(),
                'finance_stats' => $this->getFinanceStats(),
                'revenue_chart' => $this->getRevenueChartData(),
                'project_status_chart' => $this->getProjectStatusChartData(),
                'recent_projects' => $this->getRecentProjects($user),
                'recent_activities' => $this->getRecentActivities(),
                'upcoming_meetings' => $this->getUpcomingMeetings($user),
                'upcoming_holidays' => $this->getUpcomingHolidays(),
                'my_tasks' => $this->getMyTasks($user),
                'top_clients' => $this->getTopClients(),
                'team_workload' => $this->getTeamWorkload(),
                'user_widget_data' => $this->getUserWidgetData($user),
            ];
        });
    }

    /**
     * Clear the dashboard cache for a user or globally.
     */
    public function clearCache(?int $userId = null): void
    {
        if ($userId) {
            Cache::forget("dashboard_user_{$userId}_data");
        }
    }

    /**
     * HR and Attendance summary statistics.
     */
    public function getHrStats(): array
    {
        $totalEmployees = User::where('status', EmployeeStatusEnum::ACTIVE->value)->count();

        $presentToday = Attendance::whereDate('date', today())
            ->whereNotNull('check_in')
            ->count();

        $onLeaveToday = LeaveRequest::where('status', LeaveRequestStatusEnum::APPROVED->value)
            ->whereDate('from_date', '<=', today())
            ->whereDate('to_date', '>=', today())
            ->count();

        $lateToday = Attendance::whereDate('date', today())
            ->where(function ($q) {
                $q->where('status', AttendanceStatusEnum::LATE->value)
                    ->orWhere('late_minutes', '>', 0);
            })
            ->count();

        return [
            'total_employees' => $totalEmployees,
            'present_today' => $presentToday,
            'on_leave_today' => $onLeaveToday,
            'late_today' => $lateToday,
        ];
    }


    /**
     * Project, Client, and Task statistics.
     */
    public function getProjectStats(): array
    {
        $activeProjects = Project::whereIn('status', [
            ProjectStatusEnum::ACTIVE->value,
            ProjectStatusEnum::PLANNING->value,
        ])->count();

        $newProjectsThisWeek = Project::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();

        $pendingTasks = Task::whereIn('status', [
            TaskStatusEnum::TODO->value,
            TaskStatusEnum::IN_PROGRESS->value,
            TaskStatusEnum::REVIEW->value,
        ])->count();

        $overdueTasks = Task::whereIn('status', [
            TaskStatusEnum::TODO->value,
            TaskStatusEnum::IN_PROGRESS->value,
            TaskStatusEnum::REVIEW->value,
        ])
            ->whereDate('due_date', '<', today())
            ->count();

        $totalClients = Client::count();

        $newClientsThisMonth = Client::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'active_projects' => $activeProjects,
            'new_projects_this_week' => $newProjectsThisWeek,
            'pending_tasks' => $pendingTasks,
            'overdue_tasks' => $overdueTasks,
            'total_clients' => $totalClients,
            'new_clients_this_month' => $newClientsThisMonth,
        ];
    }

    /**
     * Finance statistics (Revenue, growth vs last month).
     */
    public function getFinanceStats(): array
    {
        $totalRevenue = (float) Invoice::where('status', '!=', InvoiceStatusEnum::CANCELLED->value)
            ->sum('paid_amount');

        $thisMonthRevenue = (float) Transaction::where('type', TransactionTypeEnum::INCOME->value)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $lastMonthRevenue = (float) Transaction::where('type', TransactionTypeEnum::INCOME->value)
            ->whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->sum('amount');

        $growthPercent = 0.0;
        if ($lastMonthRevenue > 0) {
            $growthPercent = round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1);
        } elseif ($thisMonthRevenue > 0) {
            $growthPercent = 100.0;
        }

        return [
            'total_revenue' => $totalRevenue,
            'this_month_revenue' => $thisMonthRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'growth_percent' => $growthPercent,
            'growth_is_positive' => $growthPercent >= 0,
        ];
    }

    /**
     * Monthly Revenue vs Expense for the last 6 months (Chart.js datasets).
     */
    public function getRevenueChartData(): array
    {
        $labels = [];
        $revenues = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;

            $labels[] = $date->format('M');

            $rev = (float) Transaction::where('type', TransactionTypeEnum::INCOME->value)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            $exp = (float) Transaction::where('type', TransactionTypeEnum::EXPENSE->value)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            $revenues[] = round($rev, 2);
            $expenses[] = round($exp, 2);
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'expenses' => $expenses,
        ];
    }

    /**
     * Project breakdown by status for doughnut chart.
     */
    public function getProjectStatusChartData(): array
    {
        $statuses = ProjectStatusEnum::cases();
        $labels = [];
        $data = [];
        $colors = [];
        $total = 0;

        foreach ($statuses as $status) {
            $count = Project::where('status', $status->value)->count();
            $labels[] = $status->label();
            $data[] = $count;
            $colors[] = $status->color();
            $total += $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
            'total' => $total,
        ];
    }

    /**
     * Recent projects (scoped by permissions/assignments if employee).
     */
    public function getRecentProjects(User $user)
    {
        $query = Project::with(['client']);

        if (! $user->can('project.view') && ! $user->hasRole('Super Admin')) {
            $query->whereHas('members', function ($q) use ($user) {
                $q->where('employee_id', $user->id);
            });
        }

        return $query->latest('id')
            ->limit(5)
            ->get();
    }

    /**
     * Recent activity log entries.
     */
    public function getRecentActivities()
    {
        return ActivityLog::with('user')
            ->latest('id')
            ->limit(6)
            ->get();
    }

    /**
     * Upcoming meetings from today onwards.
     */
    public function getUpcomingMeetings(User $user)
    {
        $query = Meeting::with(['organizer', 'project'])
            ->whereDate('date', '>=', today());

        if (! $user->can('meeting.view') && ! $user->hasRole('Super Admin')) {
            $query->forUser($user->id);
        }

        return $query->orderBy('date')
            ->orderBy('start_time')
            ->limit(4)
            ->get();
    }

    /**
     * Upcoming active holidays from today onwards.
     */
    public function getUpcomingHolidays()
    {
        return Holiday::active()
            ->whereDate('to_date', '>=', today())
            ->orderBy('from_date')
            ->limit(4)
            ->get();
    }

    /**
     * Tasks assigned to the current user that are pending.
     */
    public function getMyTasks(User $user)
    {
        return Task::whereHas('assignees', function ($q) use ($user) {
            $q->where('employee_id', $user->id);
        })
            ->whereIn('status', [
                TaskStatusEnum::TODO->value,
                TaskStatusEnum::IN_PROGRESS->value,
                TaskStatusEnum::REVIEW->value,
            ])
            ->with(['project'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();
    }

    /**
     * Top 4 clients based on paid invoice revenue.
     */
    public function getTopClients()
    {
        return Client::withCount('projects')
            ->withSum('invoices as total_paid', 'paid_amount')
            ->orderByDesc('total_paid')
            ->limit(4)
            ->get();
    }

    /**
     * Team members workload ranking by active pending tasks.
     */
    public function getTeamWorkload()
    {
        $activeEmployees = User::where('status', EmployeeStatusEnum::ACTIVE->value)
            ->withCount(['assignedTasks as pending_tasks_count' => function ($q) {
                $q->whereIn('status', [
                    TaskStatusEnum::TODO->value,
                    TaskStatusEnum::IN_PROGRESS->value,
                    TaskStatusEnum::REVIEW->value,
                ]);
            }])
            ->orderByDesc('pending_tasks_count')
            ->limit(4)
            ->get();

        $maxTasks = max(1, $activeEmployees->max('pending_tasks_count') ?? 1);

        return $activeEmployees->map(function ($employee) use ($maxTasks) {
            $count = $employee->pending_tasks_count;
            $percent = min(100, (int) round(($count / $maxTasks) * 100));

            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'avatar_url' => $employee->avatar_url,
                'tasks_count' => $count,
                'percent' => $percent,
            ];
        });
    }

    /**
     * User-specific widget info (attendance, leave stats).
     */
    public function getUserWidgetData(User $user): array
    {
        $todayAttendance = Attendance::where('employee_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $thisMonthPresent = Attendance::where('employee_id', $user->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->whereNotNull('check_in')
            ->count();

        $thisMonthLate = Attendance::where('employee_id', $user->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where(function ($q) {
                $q->where('status', AttendanceStatusEnum::LATE->value)
                    ->orWhere('late_minutes', '>', 0);
            })
            ->count();


        $pendingLeaves = LeaveRequest::where('employee_id', $user->id)
            ->where('status', LeaveRequestStatusEnum::PENDING->value)
            ->count();

        $completedTasksThisMonth = Task::whereHas('assignees', function ($q) use ($user) {
            $q->where('employee_id', $user->id);
        })
            ->where('status', TaskStatusEnum::DONE->value)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        return [
            'today_attendance' => $todayAttendance,
            'this_month_present' => $thisMonthPresent,
            'this_month_late' => $thisMonthLate,
            'pending_leaves' => $pendingLeaves,
            'completed_tasks_this_month' => $completedTasksThisMonth,
        ];
    }
}
