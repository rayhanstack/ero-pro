@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    {{-- Personal Attendance Punch Banner --}}
    @can('attendance.view')
        @php
            $dashTodayAtt = $user_widget_data['today_attendance'] ?? null;
            $dashCheckedIn = $dashTodayAtt && $dashTodayAtt->check_in !== null;
            $dashCheckedOut = $dashTodayAtt && $dashTodayAtt->check_out !== null;
        @endphp
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 {{ $dashCheckedOut ? 'bg-success-subtle text-success' : ($dashCheckedIn ? 'bg-warning-subtle text-warning-emphasis' : 'bg-primary-subtle text-primary') }} fs-4">
                        <i class="bi {{ $dashCheckedOut ? 'bi-check-circle-fill' : ($dashCheckedIn ? 'bi-stopwatch' : 'bi-clock') }}"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">{{ _trans('common.Today\'s Attendance') }}</h6>
                        <span class="text-muted small">
                            @if (! $dashCheckedIn)
                                {{ _trans('common.You have not checked in yet today.') }}
                            @elseif ($dashCheckedIn && ! $dashCheckedOut)
                                {{ _trans('common.Checked in at') }} <strong class="text-dark font-monospace">{{ $dashTodayAtt->check_in_time }}</strong> ({{ _trans('common.Working duration') }}: <strong class="text-success">{{ $dashTodayAtt->work_duration_formatted }}</strong>)
                            @else
                                {{ _trans('common.Day completed') }} ({{ $dashTodayAtt->check_in_time }} - {{ $dashTodayAtt->check_out_time }}) • <strong class="text-success">{{ $dashTodayAtt->work_duration_formatted }}</strong>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if (! $dashCheckedIn)
                        <form method="POST" action="{{ route('attendances.punch') }}" class="m-0">
                            @csrf
                            <input type="hidden" name="type" value="in">
                            <button type="submit" class="btn btn-success btn-sm px-3 py-2 rounded-3 fw-semibold shadow-xs d-flex align-items-center gap-1.5">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span>{{ _trans('common.Punch In Now') }}</span>
                            </button>
                        </form>
                    @elseif ($dashCheckedIn && ! $dashCheckedOut)
                        <form method="POST" action="{{ route('attendances.punch') }}" class="m-0">
                            @csrf
                            <input type="hidden" name="type" value="out">
                            <button type="submit" class="btn btn-warning btn-sm px-3 py-2 rounded-3 fw-semibold text-dark shadow-xs d-flex align-items-center gap-1.5">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>{{ _trans('common.Punch Out') }}</span>
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('attendances.my') }}" class="btn btn-light btn-sm border px-3 py-2 rounded-3 d-flex align-items-center gap-1">
                        <span>{{ _trans('common.My Sheet') }}</span>
                        <i class="bi bi-arrow-right small"></i>
                    </a>
                </div>
            </div>
        </div>
    @endcan

    {{-- Section 1: Executive KPI Stat Cards --}}
    <div class="row g-4 mb-4">
        @if(auth()->user()->can('finance.view') || auth()->user()->hasRole('Super Admin'))
            {{-- Revenue KPI --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 p-3 border-0 shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-muted mb-1 small fw-semibold">{{ _trans('common.Total Revenue') }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ currency_format($finance_stats['total_revenue']) }}</h3>
                        </div>
                        <div class="icon-circle bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-currency-dollar fs-5"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        @if($finance_stats['growth_is_positive'])
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                                <i class="bi bi-arrow-up-short"></i> +{{ $finance_stats['growth_percent'] }}%
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">
                                <i class="bi bi-arrow-down-short"></i> {{ $finance_stats['growth_percent'] }}%
                            </span>
                        @endif
                        <span class="text-muted extra-small ms-1">{{ _trans('common.vs last month') }}</span>
                    </div>
                </div>
            </div>
        @else
            {{-- Employee: My Pending Tasks --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 p-3 border-0 shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-muted mb-1 small fw-semibold">{{ _trans('common.My Pending Tasks') }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ count($my_tasks) }}</h3>
                        </div>
                        <div class="icon-circle bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-check2-square fs-5"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">{{ _trans('common.Assigned to you') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if(auth()->user()->can('project.view') || auth()->user()->hasRole('Super Admin'))
            {{-- Active Projects KPI --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 p-3 border-0 shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-muted mb-1 small fw-semibold">{{ _trans('common.Active Projects') }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $project_stats['active_projects'] }}</h3>
                        </div>
                        <div class="icon-circle bg-gradient-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-folder2-open fs-5"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill">
                            {{ $project_stats['new_projects_this_week'] }} {{ _trans('common.new this week') }}
                        </span>
                    </div>
                </div>
            </div>
        @else
            {{-- Employee: Completed Tasks This Month --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 p-3 border-0 shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-muted mb-1 small fw-semibold">{{ _trans('common.Tasks Done') }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $user_widget_data['completed_tasks_this_month'] }}</h3>
                        </div>
                        <div class="icon-circle bg-gradient-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-check-circle fs-5"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill">{{ _trans('common.This month') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if(auth()->user()->can('task.view') || auth()->user()->hasRole('Super Admin'))
            {{-- Pending Tasks KPI --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 p-3 border-0 shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-muted mb-1 small fw-semibold">{{ _trans('common.Pending Tasks') }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $project_stats['pending_tasks'] }}</h3>
                        </div>
                        <div class="icon-circle bg-gradient-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-list-task fs-5"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        @if($project_stats['overdue_tasks'] > 0)
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">
                                <i class="bi bi-exclamation-circle"></i> {{ $project_stats['overdue_tasks'] }} {{ _trans('common.overdue') }}
                            </span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                                <i class="bi bi-check-all"></i> {{ _trans('common.All on schedule') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @else
            {{-- Employee: Monthly Attendance Days --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 p-3 border-0 shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-muted mb-1 small fw-semibold">{{ _trans('common.Monthly Attendance') }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $user_widget_data['this_month_present'] }} <span class="fs-6 fw-normal text-muted">{{ _trans('common.Days') }}</span></h3>
                        </div>
                        <div class="icon-circle bg-gradient-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-calendar2-check fs-5"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill">
                            {{ $user_widget_data['this_month_late'] }} {{ _trans('common.late entries') }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        @if(auth()->user()->can('client.view') || auth()->user()->hasRole('Super Admin'))
            {{-- Total Clients KPI --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 p-3 border-0 shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-muted mb-1 small fw-semibold">{{ _trans('common.Total Clients') }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $project_stats['total_clients'] }}</h3>
                        </div>
                        <div class="icon-circle bg-gradient-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-people fs-5"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                            <i class="bi bi-arrow-up-short"></i> +{{ $project_stats['new_clients_this_month'] }} {{ _trans('common.this month') }}
                        </span>
                    </div>
                </div>
            </div>
        @else
            {{-- Employee: Leave Requests --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 p-3 border-0 shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="text-muted mb-1 small fw-semibold">{{ _trans('common.Pending Leaves') }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $user_widget_data['pending_leaves'] }}</h3>
                        </div>
                        <div class="icon-circle bg-gradient-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-calendar-event fs-5"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('leaves.my') }}" class="badge bg-primary bg-opacity-10 text-primary text-decoration-none rounded-pill">
                            {{ _trans('common.Apply Leave') }} &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- HR Live Attendance Overview Bar (For Admins / HR) --}}
    @if(auth()->user()->can('employee.view') || auth()->user()->hasRole('Super Admin'))
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="p-2 rounded-3 bg-primary-subtle text-primary fs-5">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ _trans('common.Today\'s Workforce Overview') }}</h6>
                                <small class="text-muted">{{ _trans('common.Real-time daily presence, leave and punctuality metrics') }}</small>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2 px-3 py-1.5 bg-light rounded-3">
                                <span class="badge bg-primary rounded-circle p-1"> </span>
                                <span class="small text-muted">{{ _trans('common.Total Staff') }}:</span>
                                <strong class="text-dark">{{ $hr_stats['total_employees'] }}</strong>
                            </div>
                            <div class="d-flex align-items-center gap-2 px-3 py-1.5 bg-success-subtle rounded-3">
                                <span class="badge bg-success rounded-circle p-1"> </span>
                                <span class="small text-success-emphasis">{{ _trans('common.Present') }}:</span>
                                <strong class="text-success">{{ $hr_stats['present_today'] }}</strong>
                            </div>
                            <div class="d-flex align-items-center gap-2 px-3 py-1.5 bg-warning-subtle rounded-3">
                                <span class="badge bg-warning rounded-circle p-1"> </span>
                                <span class="small text-warning-emphasis">{{ _trans('common.On Leave') }}:</span>
                                <strong class="text-warning-emphasis">{{ $hr_stats['on_leave_today'] }}</strong>
                            </div>
                            <div class="d-flex align-items-center gap-2 px-3 py-1.5 bg-danger-subtle rounded-3">
                                <span class="badge bg-danger rounded-circle p-1"> </span>
                                <span class="small text-danger-emphasis">{{ _trans('common.Late Today') }}:</span>
                                <strong class="text-danger">{{ $hr_stats['late_today'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Section 2: Charts Row --}}
    @if(auth()->user()->can('finance.view') || auth()->user()->can('project.view') || auth()->user()->hasRole('Super Admin'))
        <div class="row g-4 mb-4">
            {{-- Revenue vs Expense Line Chart --}}
            <div class="col-xl-8">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title fw-bold mb-0 text-dark">{{ _trans('common.Revenue vs Expense Overview') }}</h5>
                                <small class="text-muted">{{ _trans('common.Cash flow comparison for the last 6 months') }}</small>
                            </div>
                            <div class="d-flex align-items-center gap-3 extra-small fw-semibold">
                                <span class="d-flex align-items-center gap-1.5">
                                    <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #4f46e5;"></span>
                                    <span>{{ _trans('common.Revenue') }}</span>
                                </span>
                                <span class="d-flex align-items-center gap-1.5">
                                    <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #ef4444;"></span>
                                    <span>{{ _trans('common.Expense') }}</span>
                                </span>
                            </div>
                        </div>
                        <div style="position: relative; height: 290px; width: 100%;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Project Status Doughnut Chart --}}
            <div class="col-xl-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-1 text-dark">{{ _trans('common.Project Status') }}</h5>
                        <small class="text-muted d-block mb-3">{{ _trans('common.Distribution of projects by progress') }}</small>
                        <div style="position: relative; height: 260px; width: 100%; display: flex; align-items: center; justify-content: center;">
                            <canvas id="statusChart"></canvas>
                            <div class="position-absolute text-center" style="top: 45%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;">
                                <h3 class="fw-bold mb-0 text-dark">{{ $project_status_chart['total'] }}</h3>
                                <span class="text-muted extra-small text-uppercase fw-semibold">{{ _trans('common.Projects') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Section 3: Projects Table + Activity Feed / My Tasks --}}
    <div class="row g-4 mb-4">
        {{-- Recent Projects Table --}}
        <div class="col-xl-7">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title fw-bold mb-0 text-dark">{{ _trans('common.Recent Projects') }}</h5>
                            <small class="text-muted">{{ _trans('common.Latest client and internal projects') }}</small>
                        </div>
                        @can('project.view')
                            <a href="{{ route('projects.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">{{ _trans('common.View All') }}</a>
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light extra-small text-muted text-uppercase">
                                <tr>
                                    <th class="ps-4">{{ _trans('common.Project') }}</th>
                                    <th>{{ _trans('common.Client') }}</th>
                                    <th>{{ _trans('common.Deadline') }}</th>
                                    <th>{{ _trans('common.Status') }}</th>
                                    <th>{{ _trans('common.Progress') }}</th>
                                    <th class="pe-4 text-end">{{ _trans('common.Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_projects as $project)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary fw-bold small">
                                                    {{ substr($project->code, 0, 4) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('projects.show', $project) }}" class="text-decoration-none text-dark fw-bold small text-truncate d-block" style="max-width: 150px;">
                                                        {{ $project->name }}
                                                    </a>
                                                    <span class="extra-small text-muted">{{ $project->code }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="small text-muted">{{ $project->client?->company_name ?? _trans('common.Internal') }}</span>
                                        </td>
                                        <td>
                                            <span class="small font-monospace">{{ $project->deadline ? $project->deadline->format('M d') : '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $project->status->badgeClass() }} rounded-pill small">
                                                {{ $project->status->label() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center" style="min-width: 90px;">
                                                <span class="me-2 small font-monospace">{{ $project->progress }}%</span>
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $project->progress }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-light text-primary rounded-circle" title="{{ _trans('common.View Details') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small">
                                            {{ _trans('common.No active projects found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Recent Activity Feed (or My Tasks if employee) --}}
        <div class="col-xl-5">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title fw-bold mb-0 text-dark">{{ _trans('common.Recent Activity') }}</h5>
                        @can('setting.view')
                            <a href="{{ route('activity-logs.index') }}" class="extra-small text-primary text-decoration-none fw-semibold">{{ _trans('common.Full Audit') }} &rarr;</a>
                        @endcan
                    </div>
                    <div class="timeline-feed">
                        @forelse($recent_activities as $activity)
                            <div class="timeline-item">
                                <div class="timeline-dot bg-primary"></div>
                                <div class="timeline-content small">
                                    <strong class="text-dark">{{ $activity->user?->name ?? _trans('common.System') }}</strong>:
                                    <span class="text-muted">{{ $activity->action }}</span>
                                </div>
                                <div class="timeline-time extra-small text-muted">{{ $activity->created_at ? $activity->created_at->diffForHumans() : '' }}</div>
                            </div>
                        @empty
                            <p class="text-muted small py-4 text-center mb-0">{{ _trans('common.No recent activities logged.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 4: Bottom Widgets Row --}}
    <div class="row g-4 mb-4">
        {{-- Widget 1: Top Clients (or Upcoming Holidays) --}}
        @if(auth()->user()->can('client.view') || auth()->user()->hasRole('Super Admin'))
            <div class="col-xl-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title fw-bold mb-0 text-dark">{{ _trans('common.Top Clients') }}</h5>
                            <a href="{{ route('clients.index') }}" class="extra-small text-primary text-decoration-none fw-semibold">{{ _trans('common.View All') }} &rarr;</a>
                        </div>
                        @forelse($top_clients as $index => $client)
                            <div class="d-flex align-items-center mb-3.5 pb-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                <span class="text-muted extra-small fw-bold me-2.5" style="width: 20px;">#{{ $index + 1 }}</span>
                                <img src="{{ $client->logo_url }}" class="rounded-circle me-3 object-fit-cover flex-shrink-0" width="38" height="38" alt="{{ $client->company_name }}">
                                <div class="flex-grow-1 min-w-0">
                                    <h6 class="mb-0 fw-bold small text-dark text-truncate">{{ $client->company_name }}</h6>
                                    <span class="extra-small text-muted">{{ $client->projects_count }} {{ _trans('common.Projects') }}</span>
                                </div>
                                <div class="fw-bold small text-dark text-end">
                                    {{ currency_format($client->total_paid ?? 0) }}
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small py-4 text-center mb-0">{{ _trans('common.No clients found.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            {{-- Upcoming Holidays for Employee --}}
            <div class="col-xl-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title fw-bold mb-0 text-dark">{{ _trans('common.Upcoming Holidays') }}</h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">{{ count($upcoming_holidays) }}</span>
                        </div>
                        @forelse($upcoming_holidays as $holiday)
                            <div class="p-3 border rounded-3 mb-2.5 bg-light-subtle">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0 fw-bold small text-dark">{{ $holiday->title }}</h6>
                                    <span class="badge bg-primary bg-opacity-10 text-primary extra-small">{{ $holiday->type->label() }}</span>
                                </div>
                                <div class="extra-small text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $holiday->from_date ? $holiday->from_date->format('M d, Y') : '' }}
                                    @if($holiday->to_date && $holiday->to_date != $holiday->from_date)
                                        - {{ $holiday->to_date->format('M d, Y') }}
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small py-4 text-center mb-0">{{ _trans('common.No upcoming holidays.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        {{-- Widget 2: Upcoming Meetings & Holidays --}}
        <div class="col-xl-4">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title fw-bold mb-0 text-dark">{{ _trans('common.Upcoming Meetings') }}</h5>
                        @can('meeting.view')
                            <a href="{{ route('meetings.index') }}" class="extra-small text-primary text-decoration-none fw-semibold">{{ _trans('common.Calendar') }} &rarr;</a>
                        @endcan
                    </div>
                    @forelse($upcoming_meetings as $meeting)
                        <div class="p-3 border rounded-3 mb-2.5 {{ $meeting->date?->isToday() ? 'border-primary bg-primary bg-opacity-10' : 'bg-light' }}">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0 fw-bold small text-dark">{{ $meeting->title }}</h6>
                                @if($meeting->date?->isToday())
                                    <span class="badge bg-primary rounded-pill extra-small">{{ _trans('common.Today') }}</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-25 text-dark rounded-pill extra-small">{{ $meeting->date?->format('M d') }}</span>
                                @endif
                            </div>
                            <div class="extra-small text-muted d-flex align-items-center gap-2">
                                <span><i class="bi bi-clock me-1"></i>{{ $meeting->formatted_time_range }}</span>
                                @if($meeting->location)
                                    <span>• <i class="bi bi-geo-alt me-0.5"></i>{{ $meeting->location }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small py-4 text-center mb-0">{{ _trans('common.No upcoming meetings scheduled.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Widget 3: Team Workload (Admin) or My Assigned Tasks (Employee) --}}
        @if(auth()->user()->can('employee.view') || auth()->user()->hasRole('Super Admin'))
            <div class="col-xl-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-4 text-dark">{{ _trans('common.Team Workload') }}</h5>
                        @forelse($team_workload as $member)
                            <div class="mb-3.5">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $member['avatar_url'] }}" class="rounded-circle me-2 object-fit-cover" width="30" height="30" alt="{{ $member['name'] }}">
                                        <h6 class="mb-0 fw-semibold small text-dark">{{ $member['name'] }}</h6>
                                    </div>
                                    <span class="extra-small fw-bold text-muted">{{ $member['tasks_count'] }} {{ _trans('common.Tasks') }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar {{ $member['percent'] > 75 ? 'bg-danger' : ($member['percent'] > 40 ? 'bg-warning' : 'bg-success') }}" role="progressbar" style="width: {{ $member['percent'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small py-4 text-center mb-0">{{ _trans('common.No active team workload metrics.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            {{-- My Assigned Tasks --}}
            <div class="col-xl-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title fw-bold mb-0 text-dark">{{ _trans('common.My Tasks') }}</h5>
                            <a href="{{ route('tasks.index') }}" class="extra-small text-primary text-decoration-none fw-semibold">{{ _trans('common.View All') }} &rarr;</a>
                        </div>
                        @forelse($my_tasks as $task)
                            <div class="p-2.5 border rounded-3 mb-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-semibold small text-dark text-truncate" style="max-width: 170px;">{{ $task->title }}</h6>
                                    <span class="badge {{ $task->status->badgeClass() }} extra-small">{{ $task->status->label() }}</span>
                                </div>
                                <div class="extra-small text-muted d-flex justify-content-between">
                                    <span>{{ $task->project?->name ?? _trans('common.General') }}</span>
                                    <span><i class="bi bi-clock me-0.5"></i>{{ $task->due_date ? $task->due_date->format('M d') : '-' }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small py-4 text-center mb-0">{{ _trans('common.No pending tasks assigned.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            // Revenue vs Expense Line Chart
            if (document.getElementById('revenueChart')) {
                var ctxArea = document.getElementById('revenueChart').getContext('2d');

                var revenueGradient = ctxArea.createLinearGradient(0, 0, 0, 300);
                revenueGradient.addColorStop(0, 'rgba(79, 70, 229, 0.35)');
                revenueGradient.addColorStop(1, 'rgba(79, 70, 229, 0.02)');

                var expenseGradient = ctxArea.createLinearGradient(0, 0, 0, 300);
                expenseGradient.addColorStop(0, 'rgba(239, 68, 68, 0.25)');
                expenseGradient.addColorStop(1, 'rgba(239, 68, 68, 0.01)');

                var chartLabels = @json($revenue_chart['labels'] ?? []);
                var revenueData = @json($revenue_chart['revenues'] ?? []);
                var expenseData = @json($revenue_chart['expenses'] ?? []);

                new Chart(ctxArea, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: '{{ _trans("common.Revenue") }}',
                                data: revenueData,
                                backgroundColor: revenueGradient,
                                borderColor: '#4f46e5',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#4f46e5',
                                pointHoverBackgroundColor: '#4f46e5',
                                pointHoverBorderColor: '#ffffff',
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: '{{ _trans("common.Expense") }}',
                                data: expenseData,
                                backgroundColor: expenseGradient,
                                borderColor: '#ef4444',
                                borderWidth: 2,
                                borderDash: [4, 4],
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#ef4444',
                                pointHoverBackgroundColor: '#ef4444',
                                pointHoverBorderColor: '#ffffff',
                                pointRadius: 3,
                                pointHoverRadius: 5
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 10,
                                titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13 },
                                bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13, weight: 'bold' },
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + '{{ currency_symbol() }}' + Number(context.parsed.y).toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false, drawBorder: false },
                                ticks: { font: { family: "'Plus Jakarta Sans', sans-serif" }, color: '#64748b' }
                            },
                            y: {
                                grid: { borderDash: [4, 4], color: '#f1f5f9', drawBorder: false },
                                ticks: {
                                    font: { family: "'Plus Jakarta Sans', sans-serif" },
                                    color: '#64748b',
                                    callback: function(value) {
                                        return value >= 1000 ? '{{ currency_symbol() }}' + (value / 1000) + 'k' : '{{ currency_symbol() }}' + value;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Project Status Doughnut Chart
            if (document.getElementById('statusChart')) {
                var ctxDoughnut = document.getElementById('statusChart').getContext('2d');
                var statusLabels = @json($project_status_chart['labels'] ?? []);
                var statusData = @json($project_status_chart['data'] ?? []);
                var statusColors = @json($project_status_chart['colors'] ?? []);

                new Chart(ctxDoughnut, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusData,
                            backgroundColor: statusColors,
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 14,
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                                    color: '#64748b'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + ' {{ _trans("common.projects") }}';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
