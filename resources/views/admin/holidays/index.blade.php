@extends('admin.layouts.app')
@section('title', _trans('common.Holidays'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Holidays') }}"
        subtitle="{{ _trans('common.Manage public holidays, company breaks, and view holiday calendars') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Holidays')],
        ]"
    >
        <x-slot:actions>
            @can('holiday.create')
                <a href="{{ route('holidays.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Add Holiday') }}</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Filter Card --}}
    <x-ui.card class="mb-4">
        <form method="GET" action="{{ route('holidays.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3 col-lg-3">
                <label for="year" class="form-label small fw-semibold text-muted">{{ _trans('common.Year') }}</label>
                <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                    @foreach ($availableYears as $year)
                        <option value="{{ $year }}" {{ $currentYear === $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 col-lg-3">
                <label for="search" class="form-label small fw-semibold text-muted">{{ _trans('common.Search Title') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text"
                        name="search"
                        id="search"
                        class="form-control"
                        placeholder="{{ _trans('common.Search holiday...') }}"
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3 col-lg-2">
                <label for="type" class="form-label small fw-semibold text-muted">{{ _trans('common.Type') }}</label>
                <select name="type" id="type" class="form-select">
                    <option value="">{{ _trans('common.All Types') }}</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 col-lg-2">
                <label for="status" class="form-label small fw-semibold text-muted">{{ _trans('common.Status') }}</label>
                <select name="status" id="status" class="form-select">
                    <option value="">{{ _trans('common.All Statuses') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-funnel"></i>
                    <span>{{ _trans('common.Filter') }}</span>
                </button>
                @if (request()->hasAny(['search', 'type', 'status']))
                    <a href="{{ route('holidays.index', ['year' => $currentYear]) }}" class="btn btn-outline-secondary" title="{{ _trans('common.Reset') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-ui.card>

    <div class="row g-4">
        {{-- Left: Holidays Table List --}}
        <div class="col-lg-8">
            <x-ui.card :title="_trans('common.Holiday List') . ' (' . $currentYear . ')'" icon="bi-calendar-event">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4">#</th>
                                <th class="py-3 px-4">{{ _trans('common.Holiday Title') }}</th>
                                <th class="py-3 px-4">{{ _trans('common.Date Range') }}</th>
                                <th class="py-3 px-4 text-center">{{ _trans('common.Days') }}</th>
                                <th class="py-3 px-4 text-center">{{ _trans('common.Type') }}</th>
                                <th class="py-3 px-4 text-end">{{ _trans('common.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($holidays as $holiday)
                                <tr>
                                    <td class="py-3 px-4 text-muted">{{ $loop->iteration + ($holidays->currentPage() - 1) * $holidays->perPage() }}</td>
                                    <td class="py-3 px-4">
                                        <div class="fw-semibold text-dark">{{ $holiday->title }}</div>
                                        @if ($holiday->description)
                                            <div class="text-muted small text-truncate" style="max-width: 200px;">{{ $holiday->description }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-monospace small">
                                        {{ formatDate($holiday->from_date) }}
                                        @if ($holiday->from_date->ne($holiday->to_date))
                                            <span class="text-muted">&rarr;</span> {{ formatDate($holiday->to_date) }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">
                                            {{ $holiday->days_count }} {{ $holiday->days_count === 1 ? _trans('common.day') : _trans('common.days') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="{{ $holiday->type?->badgeClass() ?? 'badge bg-light' }}">
                                            {{ $holiday->type?->label() ?? ucfirst($holiday->type) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            @can('holiday.edit')
                                                <a href="{{ route('holidays.edit', $holiday) }}" class="btn btn-sm btn-outline-primary" title="{{ _trans('common.Edit') }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            @endcan
                                            @can('holiday.delete')
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmDeleteModal"
                                                    data-action="{{ route('holidays.destroy', $holiday) }}"
                                                    data-item-name="{{ $holiday->title }}"
                                                    title="{{ _trans('common.Delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        <span class="fw-medium">{{ _trans('common.No holidays scheduled for this year.') }}</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($holidays->hasPages())
                    <div class="px-4 py-3 border-top">
                        <x-ui.pagination :paginator="$holidays" />
                    </div>
                @endif
            </x-ui.card>
        </div>

        {{-- Right: Simple Month Calendar View --}}
        <div class="col-lg-4">
            <x-ui.card :title="_trans('common.Month Calendar')" icon="bi-calendar3">
                @php
                    $monthCarbon = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1);
                    $daysInMonth = $monthCarbon->daysInMonth;
                    $startDayOfWeek = $monthCarbon->dayOfWeek; // 0=Sun, ..., 6=Sat
                    $prevMonth = $monthCarbon->copy()->subMonth();
                    $nextMonth = $monthCarbon->copy()->addMonth();

                    $holidayDatesInMonth = [];
                    foreach ($monthHolidays as $h) {
                        $period = \Carbon\CarbonPeriod::create(
                            max($h->from_date, $monthCarbon->copy()->startOfMonth()),
                            min($h->to_date, $monthCarbon->copy()->endOfMonth())
                        );
                        foreach ($period as $d) {
                            $holidayDatesInMonth[$d->format('j')] = $h->title;
                        }
                    }
                @endphp

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <a href="{{ route('holidays.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <h6 class="mb-0 fw-bold">{{ $monthCarbon->format('F Y') }}</h6>
                    <a href="{{ route('holidays.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

                <div class="calendar-mini-grid border rounded p-2 bg-light">
                    <div class="row g-1 text-center fw-semibold text-muted small mb-2">
                        <div class="col">Su</div>
                        <div class="col">Mo</div>
                        <div class="col">Tu</div>
                        <div class="col">We</div>
                        <div class="col">Th</div>
                        <div class="col text-danger">Fr</div>
                        <div class="col">Sa</div>
                    </div>

                    <div class="row g-1 text-center small">
                        {{-- Empty offset cells --}}
                        @for ($i = 0; $i < $startDayOfWeek; $i++)
                            <div class="col p-1 text-muted opacity-25">&middot;</div>
                        @endfor

                        {{-- Day cells --}}
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $cellDate = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, $day);
                                $isWeekend = $cellDate->isFriday();
                                $isHoliday = isset($holidayDatesInMonth[$day]);
                                $isToday = $cellDate->isToday();
                            @endphp

                            <div class="col p-1">
                                <div class="rounded py-1 {{ $isHoliday ? 'bg-danger text-white fw-bold shadow-xs' : ($isToday ? 'border border-primary fw-bold text-primary' : ($isWeekend ? 'bg-secondary bg-opacity-10 text-danger' : 'bg-white')) }}"
                                    title="{{ $isHoliday ? $holidayDatesInMonth[$day] : ($isWeekend ? 'Weekend' : '') }}"
                                    data-bs-toggle="tooltip">
                                    {{ $day }}
                                </div>
                            </div>

                            @if (($startDayOfWeek + $day) % 7 === 0 && $day < $daysInMonth)
                                </div><div class="row g-1 text-center small mt-1">
                            @endif
                        @endfor

                        {{-- Trailing offset cells --}}
                        @php
                            $remaining = (7 - (($startDayOfWeek + $daysInMonth) % 7)) % 7;
                        @endphp
                        @for ($i = 0; $i < $remaining; $i++)
                            <div class="col p-1 text-muted opacity-25">&middot;</div>
                        @endfor
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-3 text-xs text-muted small">
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-danger p-1 rounded-circle" style="width: 8px; height: 8px;"></span>
                        <span>{{ _trans('common.Holiday') }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-secondary bg-opacity-25 text-danger p-1 rounded" style="width: 8px; height: 8px;"></span>
                        <span>{{ _trans('common.Weekend') }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge border border-primary p-1 rounded" style="width: 8px; height: 8px;"></span>
                        <span>{{ _trans('common.Today') }}</span>
                    </div>
                </div>

                {{-- List of holidays in this month --}}
                @if ($monthHolidays->isNotEmpty())
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-semibold small text-muted mb-2">{{ _trans('common.Holidays in this Month') }}</h6>
                        <ul class="list-unstyled mb-0 small">
                            @foreach ($monthHolidays as $mh)
                                <li class="py-1 border-bottom border-light d-flex justify-content-between">
                                    <span class="fw-medium text-dark text-truncate me-2">{{ $mh->title }}</span>
                                    <span class="text-muted font-monospace">{{ $mh->from_date->format('M j') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>

    <x-ui.confirm-delete />
@endsection
