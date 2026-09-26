@extends('admin.layouts.app')

@section('title', _trans('common.Leave Calendar'))

@section('content')
    <div class="container-fluid py-3">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ _trans('common.Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leaves.my') }}" class="text-decoration-none">{{ _trans('common.Leaves') }}</a></li>
                        <li class="breadcrumb-item active">{{ _trans('common.Leave Calendar') }}</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">{{ _trans('common.Leave Calendar') }} — {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('leaves.requests') }}" class="btn btn-outline-primary btn-sm rounded-3 d-flex align-items-center gap-1.5">
                    <i class="bi bi-inbox"></i>
                    <span>{{ _trans('common.Approval Queue') }}</span>
                </a>
                <a href="{{ route('leaves.my') }}" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-1.5 shadow-sm">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Apply Leave') }}</span>
                </a>
            </div>
        </div>

        <!-- Navigation & Filters Toolbar -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('leaves.calendar') }}" class="row g-2 align-items-center">
                    @php
                        $prevMonth = $month == 1 ? 12 : $month - 1;
                        $prevYear = $month == 1 ? $year - 1 : $year;
                        $nextMonth = $month == 12 ? 1 : $month + 1;
                        $nextYear = $month == 12 ? $year + 1 : $year;
                    @endphp

                    <!-- Month Navigation Buttons -->
                    <div class="col-md-auto d-flex gap-1">
                        <a href="{{ route('leaves.calendar', array_merge($filters, ['month' => $prevMonth, 'year' => $prevYear])) }}" class="btn btn-light btn-sm border rounded-3" title="{{ _trans('common.Previous Month') }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        <a href="{{ route('leaves.calendar', array_merge($filters, ['month' => date('n'), 'year' => date('Y')])) }}" class="btn btn-light btn-sm border rounded-3 px-2.5">
                            {{ _trans('common.Today') }}
                        </a>
                        <a href="{{ route('leaves.calendar', array_merge($filters, ['month' => $nextMonth, 'year' => $nextYear])) }}" class="btn btn-light btn-sm border rounded-3" title="{{ _trans('common.Next Month') }}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>

                    <!-- Month Select -->
                    <div class="col-md-2">
                        <select name="month" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Year Select -->
                    <div class="col-md-2">
                        <select name="year" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                            @for ($y = date('Y') + 2; $y >= date('Y') - 3; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Department -->
                    <div class="col-md-2">
                        <select name="department_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                            <option value="">{{ _trans('common.All Departments') }}</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Leave Type -->
                    <div class="col-md-2">
                        <select name="leave_type_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                            <option value="">{{ _trans('common.All Leave Types') }}</option>
                            @foreach ($leaveTypes as $lt)
                                <option value="{{ $lt->id }}" {{ ($filters['leave_type_id'] ?? '') == $lt->id ? 'selected' : '' }}>
                                    {{ $lt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-auto ms-auto d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small px-2.5 py-1">
                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>{{ _trans('common.Approved') }}
                        </span>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small px-2.5 py-1">
                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>{{ _trans('common.Pending') }}
                        </span>
                    </div>
                </form>
            </div>
        </div>

        @php
            $firstDayOfMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $daysInMonth = $firstDayOfMonth->daysInMonth;
            $startDayOfWeek = $firstDayOfMonth->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
            $totalCells = ceil(($daysInMonth + $startDayOfWeek - 1) / 7) * 7;
        @endphp

        <!-- Calendar Month Grid -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 calendar-grid" style="table-layout: fixed;">
                        <thead class="table-light text-center small text-uppercase">
                            <tr>
                                <th class="py-2.5 text-secondary" style="width: 14.28%;">{{ _trans('common.Mon') }}</th>
                                <th class="py-2.5 text-secondary" style="width: 14.28%;">{{ _trans('common.Tue') }}</th>
                                <th class="py-2.5 text-secondary" style="width: 14.28%;">{{ _trans('common.Wed') }}</th>
                                <th class="py-2.5 text-secondary" style="width: 14.28%;">{{ _trans('common.Thu') }}</th>
                                <th class="py-2.5 text-secondary" style="width: 14.28%;">{{ _trans('common.Fri') }}</th>
                                <th class="py-2.5 text-secondary" style="width: 14.28%;">{{ _trans('common.Sat') }}</th>
                                <th class="py-2.5 text-secondary" style="width: 14.28%;">{{ _trans('common.Sun') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($cell = 1; $cell <= $totalCells; $cell++)
                                @if ($cell % 7 == 1)
                                    <tr>
                                @endif

                                @php
                                    $dayNumber = $cell - $startDayOfWeek + 1;
                                    $isCurrentMonth = $dayNumber >= 1 && $dayNumber <= $daysInMonth;
                                    $dateStr = $isCurrentMonth ? sprintf('%04d-%02d-%02d', $year, $month, $dayNumber) : null;
                                    $isToday = $dateStr === date('Y-m-d');

                                    // Filter events for this date
                                    $dayEvents = [];
                                    if ($isCurrentMonth) {
                                        foreach ($events as $ev) {
                                            if ($dateStr >= $ev['start'] && $dateStr < $ev['end']) {
                                                $dayEvents[] = $ev;
                                            }
                                        }
                                    }
                                @endphp

                                <td class="p-2 align-top {{ ! $isCurrentMonth ? 'bg-light bg-opacity-50 text-muted opacity-50' : '' }} {{ $isToday ? 'bg-primary-subtle bg-opacity-25' : '' }}" style="height: 120px; vertical-align: top;">
                                    @if ($isCurrentMonth)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold small {{ $isToday ? 'badge bg-primary rounded-circle p-1 px-2 text-white' : 'text-dark' }}">
                                                {{ $dayNumber }}
                                            </span>
                                            @if (count($dayEvents) > 0)
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 9px;">
                                                    {{ count($dayEvents) }} {{ count($dayEvents) > 1 ? 'on leave' : 'on leave' }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Day Events Badges -->
                                        <div class="d-flex flex-column gap-1 overflow-y-auto" style="max-height: 80px;">
                                            @foreach ($dayEvents as $event)
                                                <div class="badge text-start text-truncate py-1 px-1.5 rounded-2 d-flex align-items-center gap-1"
                                                    style="background-color: {{ $event['color'] }}20; color: {{ $event['color'] }}; border: 1px solid {{ $event['color'] }}40; font-size: 10px; cursor: pointer;"
                                                    title="{{ $event['employee_name'] }} ({{ $event['leave_type'] }}) - {{ $event['reason'] }}">
                                                    <i class="bi bi-person-fill" style="font-size: 9px;"></i>
                                                    <span class="text-truncate fw-semibold">{{ $event['employee_name'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="small text-muted opacity-25">—</span>
                                    @endif
                                </td>

                                @if ($cell % 7 == 0)
                                    </tr>
                                @endif
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
