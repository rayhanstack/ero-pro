@extends('admin.layouts.app')

@section('title', _trans('common.Monthly Attendance Matrix'))

@section('content')
    <div class="container-fluid py-3">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ _trans('common.Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('attendances.my') }}" class="text-decoration-none">{{ _trans('common.Attendance') }}</a></li>
                        <li class="breadcrumb-item active">{{ _trans('common.Monthly Sheet') }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">{{ _trans('common.Monthly Attendance Sheet') }}</h4>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill small">
                        {{ $gridData['month_name'] }}
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('attendances.daily') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1.5 rounded-3">
                    <i class="bi bi-calendar-day"></i>
                    <span>{{ _trans('common.Daily Attendance') }}</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('attendances.monthly') }}" class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">{{ _trans('common.Month') }}</label>
                        <select name="month" class="form-select form-select-sm rounded-3">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">{{ _trans('common.Year') }}</label>
                        <select name="year" class="form-select form-select-sm rounded-3">
                            @for ($y = Carbon\Carbon::now()->year; $y >= Carbon\Carbon::now()->year - 3; $y--)
                                <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">{{ _trans('common.Department') }}</label>
                        <select name="department_id" class="form-select form-select-sm rounded-3">
                            <option value="">{{ _trans('common.All Departments') }}</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">{{ _trans('common.Search Employee') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 rounded-end-3" placeholder="{{ _trans('common.Search by name, code...') }}" value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex gap-2 align-items-end pt-3 pt-md-0">
                        <button type="submit" class="btn btn-primary btn-sm rounded-3 px-3 w-100">
                            <i class="bi bi-filter me-1"></i>{{ _trans('common.Filter') }}
                        </button>
                        @if ($month !== Carbon\Carbon::now()->month || $year !== Carbon\Carbon::now()->year || $departmentId || $search)
                            <a href="{{ route('attendances.monthly') }}" class="btn btn-light btn-sm border rounded-3 px-2" title="{{ _trans('common.Reset') }}">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendance Legend -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
            <div class="d-flex flex-wrap align-items-center gap-3 small">
                <span class="fw-bold text-dark me-2">{{ _trans('common.Status Legend') }}:</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><strong>P</strong> = {{ _trans('common.Present') }}</span>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1"><strong>L</strong> = {{ _trans('common.Late') }}</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><strong>A</strong> = {{ _trans('common.Absent') }}</span>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><strong>Lv</strong> = {{ _trans('common.Leave') }}</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><strong>H</strong> = {{ _trans('common.Holiday') }}</span>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><strong>W</strong> = {{ _trans('common.Weekend') }}</span>
                <span class="badge bg-purple-subtle text-purple border border-purple-subtle px-2 py-1"><strong>HD</strong> = {{ _trans('common.Half Day') }}</span>
            </div>
        </div>

        <!-- Monthly Grid Matrix Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="table-responsive" style="max-height: 700px;">
                <table class="table table-bordered table-sm align-middle text-center mb-0" style="font-size: 12px;">
                    <thead class="table-light sticky-top bg-light text-muted small text-uppercase" style="z-index: 10;">
                        <tr>
                            <th class="text-start ps-3" style="min-width: 220px; position: sticky; left: 0; background: #f8fafc; z-index: 11;">
                                {{ _trans('common.Employee') }}
                            </th>
                            @foreach ($gridData['days'] as $d => $dayInfo)
                                <th style="min-width: 34px;" class="{{ $dayInfo['is_weekend'] ? 'bg-secondary-subtle text-muted' : ($dayInfo['is_holiday'] ? 'bg-primary-subtle text-primary' : '') }}">
                                    <div>{{ $d }}</div>
                                    <div class="fw-normal" style="font-size: 10px;">{{ substr($dayInfo['day_name'], 0, 2) }}</div>
                                </th>
                            @endforeach
                            <th class="bg-success-subtle text-success" title="{{ _trans('common.Present') }}">P</th>
                            <th class="bg-warning-subtle text-warning-emphasis" title="{{ _trans('common.Late') }}">L</th>
                            <th class="bg-danger-subtle text-danger" title="{{ _trans('common.Absent') }}">A</th>
                            <th class="bg-info-subtle text-info" title="{{ _trans('common.Leave') }}">Lv</th>
                            <th class="bg-primary-subtle text-primary" title="{{ _trans('common.Holiday') }}">H</th>
                            <th class="bg-secondary-subtle text-secondary" title="{{ _trans('common.Weekend') }}">W</th>
                            <th class="bg-light text-dark fw-bold" title="{{ _trans('common.Total Hours') }}">{{ _trans('common.Hrs') }}</th>
                            <th class="bg-light text-dark fw-bold" title="{{ _trans('common.Overtime Hours') }}">{{ _trans('common.OT') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($gridData['matrix'] as $row)
                            @php
                                $emp = $row['employee'];
                                $counts = $row['counts'];
                            @endphp
                            <tr>
                                <td class="text-start ps-3 bg-white" style="position: sticky; left: 0; z-index: 5;">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $emp->avatar_url }}" alt="{{ $emp->name }}" class="rounded-circle object-fit-cover me-2 flex-shrink-0" width="28" height="28">
                                        <div class="text-truncate" style="max-width: 170px;">
                                            <a href="{{ route('employees.show', $emp) }}" class="fw-semibold text-dark text-decoration-none d-block text-truncate">
                                                {{ $emp->name }}
                                            </a>
                                            <span class="text-muted" style="font-size: 10px;">{{ $emp->emp_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                @foreach ($gridData['days'] as $d => $dayInfo)
                                    @php
                                        $att = $row['attendances'][$d] ?? null;
                                        $cellClass = '';
                                        $code = '-';
                                        $tooltip = $dayInfo['date'];

                                        if ($att) {
                                            $code = $att->status->shortCode();
                                            $cellClass = match ($att->status) {
                                                \App\Enums\AttendanceStatusEnum::PRESENT => 'bg-success-subtle text-success fw-bold',
                                                \App\Enums\AttendanceStatusEnum::LATE => 'bg-warning-subtle text-warning-emphasis fw-bold',
                                                \App\Enums\AttendanceStatusEnum::ABSENT => 'bg-danger-subtle text-danger fw-bold',
                                                \App\Enums\AttendanceStatusEnum::LEAVE => 'bg-info-subtle text-info fw-bold',
                                                \App\Enums\AttendanceStatusEnum::HOLIDAY => 'bg-primary-subtle text-primary fw-bold',
                                                \App\Enums\AttendanceStatusEnum::WEEKEND => 'bg-secondary-subtle text-secondary',
                                                \App\Enums\AttendanceStatusEnum::HALF_DAY => 'bg-purple-subtle text-purple fw-bold',
                                            };
                                            $tooltip = $att->status->label() . ' (' . ($att->check_in_time ?? '--') . ' - ' . ($att->check_out_time ?? '--') . ')';
                                        } elseif ($dayInfo['is_holiday']) {
                                            $cellClass = 'bg-primary-subtle text-primary';
                                            $code = 'H';
                                            $tooltip = _trans('common.Holiday');
                                        } elseif ($dayInfo['is_weekend']) {
                                            $cellClass = 'bg-secondary-subtle text-secondary';
                                            $code = 'W';
                                            $tooltip = _trans('common.Weekend');
                                        }
                                    @endphp
                                    <td class="{{ $cellClass }} p-1" title="{{ $tooltip }}">
                                        {{ $code }}
                                    </td>
                                @endforeach
                                <td class="fw-bold text-success">{{ $counts['present'] }}</td>
                                <td class="fw-bold text-warning-emphasis">{{ $counts['late'] }}</td>
                                <td class="fw-bold text-danger">{{ $counts['absent'] }}</td>
                                <td class="fw-bold text-info">{{ $counts['leave'] }}</td>
                                <td class="fw-bold text-primary">{{ $counts['holiday'] }}</td>
                                <td class="fw-bold text-secondary">{{ $counts['weekend'] }}</td>
                                <td class="fw-bold text-dark font-monospace">{{ $row['work_hours_formatted'] }}</td>
                                <td class="fw-bold text-success font-monospace">{{ $row['overtime_hours_formatted'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($gridData['days']) + 9 }}" class="text-center py-4 text-muted">
                                    <i class="bi bi-people fs-2 d-block mb-1 text-secondary"></i>
                                    {{ _trans('common.No active employees found matching criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
