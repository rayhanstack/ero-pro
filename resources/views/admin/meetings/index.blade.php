@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Meetings & Schedules'))

@section('content')
    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Meetings & Schedules') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Coordinate cross-functional sessions, client reviews, room bookings, and minutes of meeting') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- My Meetings Toggle --}}
            <a href="{{ route('meetings.index', array_merge(request()->except('page'), ['my_meetings' => request('my_meetings') ? null : 1])) }}"
                class="btn btn-sm {{ request('my_meetings') ? 'btn-primary' : 'btn-outline-secondary' }} d-inline-flex align-items-center gap-1">
                <i class="bi bi-person-check"></i>
                <span>{{ _trans('common.My Meetings') }}</span>
            </a>

            {{-- Filter Toggle Button --}}
            <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#meetingFilterCollapse">
                <i class="bi bi-filter"></i>
                <span>{{ _trans('common.Filter') }}</span>
                @if(request()->hasAny(['search', 'status', 'type', 'project_id', 'organizer_id', 'start_date', 'end_date']))
                    <span class="badge bg-primary rounded-pill ms-1">!</span>
                @endif
            </button>

            {{-- View Switcher (Calendar / List) --}}
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('meetings.index', array_merge(request()->except('page'), ['view' => 'calendar'])) }}"
                    class="btn {{ $viewMode !== 'list' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    title="{{ _trans('common.Calendar View') }}">
                    <i class="bi bi-calendar3"></i>
                    <span class="d-none d-sm-inline ms-1">{{ _trans('common.Calendar') }}</span>
                </a>
                <a href="{{ route('meetings.index', array_merge(request()->except('page'), ['view' => 'list'])) }}"
                    class="btn {{ $viewMode === 'list' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    title="{{ _trans('common.List View') }}">
                    <i class="bi bi-list-task"></i>
                    <span class="d-none d-sm-inline ms-1">{{ _trans('common.List') }}</span>
                </a>
            </div>

            {{-- Schedule Meeting Button --}}
            @can('meeting.create')
                <a href="{{ route('meetings.create') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Schedule Meeting') }}</span>
                </a>
            @endcan
        </div>
    </div>

    {{-- Filter Collapse Box --}}
    <div class="collapse {{ request()->hasAny(['search', 'status', 'type', 'project_id', 'organizer_id', 'start_date', 'end_date']) ? 'show' : '' }} mb-4" id="meetingFilterCollapse">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form method="GET" action="{{ route('meetings.index') }}" class="row g-2 align-items-end">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                @if (request('my_meetings'))
                    <input type="hidden" name="my_meetings" value="1">
                @endif

                <div class="col-md-3">
                    <label class="form-label fs-8 fw-semibold text-muted mb-1">{{ _trans('common.Search') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="{{ _trans('common.Title, agenda, room...') }}" value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-semibold text-muted mb-1">{{ _trans('common.Status') }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ _trans('common.All Statuses') }}</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" {{ request('status') == $s->value ? 'selected' : '' }}>
                                {{ $s->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-semibold text-muted mb-1">{{ _trans('common.Type') }}</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">{{ _trans('common.All Types') }}</option>
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}" {{ request('type') == $t->value ? 'selected' : '' }}>
                                {{ $t->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-semibold text-muted mb-1">{{ _trans('common.Project') }}</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">{{ _trans('common.All Projects') }}</option>
                        @foreach ($projects as $proj)
                            <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                {{ $proj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-semibold text-muted mb-1">{{ _trans('common.Organizer') }}</label>
                    <select name="organizer_id" class="form-select form-select-sm">
                        <option value="">{{ _trans('common.All Organizers') }}</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('organizer_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">{{ _trans('common.Apply') }}</button>
                    <a href="{{ route('meetings.index', ['view' => $viewMode]) }}" class="btn btn-sm btn-light border" title="{{ _trans('common.Clear') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-8 fw-semibold text-muted text-uppercase">{{ _trans('common.Total') }}</span>
                    <span class="badge bg-light text-dark border"><i class="bi bi-calendar-event"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-dark">{{ $stats['total'] ?? 0 }}</h4>
                <div class="text-muted extra-small mt-1" style="font-size: 11px;">{{ _trans('common.All meetings') }}</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-8 fw-semibold text-primary text-uppercase">{{ _trans('common.Scheduled') }}</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25"><i class="bi bi-clock-history"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-primary">{{ $stats['scheduled'] ?? 0 }}</h4>
                <div class="text-muted extra-small mt-1" style="font-size: 11px;">{{ _trans('common.Upcoming sessions') }}</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-8 fw-semibold text-success text-uppercase">{{ _trans('common.Completed') }}</span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="bi bi-check-circle"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-success">{{ $stats['completed'] ?? 0 }}</h4>
                <div class="text-muted extra-small mt-1" style="font-size: 11px;">{{ _trans('common.With minutes / finished') }}</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-8 fw-semibold text-warning text-uppercase">{{ _trans('common.Today') }}</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25"><i class="bi bi-calendar-check"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-warning">{{ $stats['today'] ?? 0 }}</h4>
                <div class="text-muted extra-small mt-1" style="font-size: 11px;">{{ _trans('common.Scheduled for today') }}</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-8 fw-semibold text-info text-uppercase">{{ _trans('common.My Meetings') }}</span>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25"><i class="bi bi-person-video2"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-info">{{ $stats['my_meetings'] ?? 0 }}</h4>
                <div class="text-muted extra-small mt-1" style="font-size: 11px;">{{ _trans('common.Invited or hosting') }}</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fs-8 fw-semibold text-danger text-uppercase">{{ _trans('common.Cancelled') }}</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="bi bi-x-octagon"></i></span>
                </div>
                <h4 class="fw-bold mb-0 text-danger">{{ $stats['cancelled'] ?? 0 }}</h4>
                <div class="text-muted extra-small mt-1" style="font-size: 11px;">{{ _trans('common.Cancelled calls') }}</div>
            </div>
        </div>
    </div>

    @if ($viewMode === 'list')
        {{-- List View Mode --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-ul text-primary me-2"></i>{{ _trans('common.Meetings List') }}</h6>
                <span class="badge bg-light text-dark border">{{ $meetings->total() }} {{ _trans('common.Total') }}</span>
            </div>

            @if ($meetings->isEmpty())
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="fw-semibold text-dark mt-3">{{ _trans('common.No meetings found') }}</h5>
                    <p class="text-muted small mb-3">{{ _trans('common.There are no meetings matching your filter criteria.') }}</p>
                    @can('meeting.create')
                        <a href="{{ route('meetings.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>{{ _trans('common.Schedule First Meeting') }}
                        </a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ _trans('common.Meeting') }}</th>
                                <th>{{ _trans('common.Date & Time') }}</th>
                                <th>{{ _trans('common.Type & Location') }}</th>
                                <th>{{ _trans('common.Organizer') }}</th>
                                <th>{{ _trans('common.Attendees & RSVP') }}</th>
                                <th>{{ _trans('common.Status') }}</th>
                                <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($meetings as $meeting)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('meetings.show', $meeting) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $meeting->title }}
                                        </a>
                                        @if ($meeting->project)
                                            <div class="text-muted small">
                                                <i class="bi bi-folder2 text-primary me-1"></i>{{ $meeting->project->name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $meeting->date ? $meeting->date->format('M d, Y') : _trans('common.N/A') }}</div>
                                        <div class="text-muted extra-small" style="font-size: 11px;">
                                            <i class="bi bi-clock me-1"></i>{{ $meeting->formatted_time_range }} ({{ $meeting->duration_minutes }}m)
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge {{ $meeting->type->badgeClass() }} py-1 px-2 mb-1">
                                                <i class="bi {{ $meeting->type->icon() }} me-1"></i>{{ $meeting->type->label() }}
                                            </span>
                                        </div>
                                        @if ($meeting->location)
                                            <span class="small text-muted text-truncate d-inline-block" style="max-width: 180px;">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $meeting->location }}
                                            </span>
                                        @elseif ($meeting->meeting_link)
                                            <a href="{{ $meeting->meeting_link }}" target="_blank" class="small text-primary text-decoration-none text-truncate d-inline-block" style="max-width: 180px;">
                                                <i class="bi bi-link-45deg me-1"></i>{{ _trans('common.Meeting Link') }}
                                            </a>
                                        @else
                                            <span class="small text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($meeting->organizer)
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $meeting->organizer->avatar_url }}" alt="{{ $meeting->organizer->name }}" class="rounded-circle object-fit-cover shadow-sm" width="28" height="28">
                                                <div>
                                                    <div class="small fw-semibold text-dark">{{ $meeting->organizer->name }}</div>
                                                    <span class="text-muted extra-small" style="font-size: 10px;">{{ $meeting->organizer->employeeDetail?->designation?->name ?? _trans('common.Host') }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="small text-muted fst-italic">{{ _trans('common.System') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5 mb-1">
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" title="{{ _trans('common.Accepted') }}">
                                                <i class="bi bi-check-lg"></i> {{ $meeting->accepted_count }}
                                            </span>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" title="{{ _trans('common.Pending') }}">
                                                <i class="bi bi-hourglass-split"></i> {{ $meeting->pending_count }}
                                            </span>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" title="{{ _trans('common.Declined') }}">
                                                <i class="bi bi-x-lg"></i> {{ $meeting->declined_count }}
                                            </span>
                                        </div>
                                        <span class="text-muted extra-small" style="font-size: 11px;">
                                            {{ $meeting->attendees->count() }} {{ _trans('common.invited') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $meeting->status->badgeClass() }} rounded-pill">
                                            {{ $meeting->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('meetings.show', $meeting) }}">
                                                        <i class="bi bi-eye text-primary me-2"></i>{{ _trans('common.View Details & Minutes') }}
                                                    </a>
                                                </li>
                                                @can('meeting.edit')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('meetings.edit', $meeting) }}">
                                                            <i class="bi bi-pencil text-info me-2"></i>{{ _trans('common.Edit') }}
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('meeting.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('meetings.destroy', $meeting) }}" onsubmit="return confirm('{{ _trans('common.Are you sure you want to cancel/delete this meeting?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash me-2"></i>{{ _trans('common.Cancel / Delete') }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($meetings->hasPages())
                    <div class="card-footer bg-white py-3 border-top">
                        {{ $meetings->links() }}
                    </div>
                @endif
            @endif
        </div>
    @else
        {{-- Calendar View Mode --}}
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5">
                        <i class="bi bi-circle-fill me-1" style="font-size: 8px; color: #4f46e5;"></i>{{ _trans('common.In-Person') }}
                    </span>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1.5">
                        <i class="bi bi-circle-fill me-1" style="font-size: 8px; color: #0ea5e9;"></i>{{ _trans('common.Online') }}
                    </span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1.5">
                        <i class="bi bi-circle-fill me-1" style="font-size: 8px; color: #8b5cf6;"></i>{{ _trans('common.Hybrid') }}
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5">
                        <i class="bi bi-circle-fill me-1" style="font-size: 8px; color: #10b981;"></i>{{ _trans('common.Completed') }}
                    </span>
                </div>
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>{{ _trans('common.Click any day to schedule, or click an event to view details.') }}
                </div>
            </div>

            <div id="meetingCalendar" style="min-height: 700px;"></div>
        </div>
    @endif

    {{-- Event Preview Quick Modal --}}
    <div class="modal fade" id="eventQuickModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="quickModalTitle">
                        <i class="bi bi-calendar-event text-primary me-2"></i><span>{{ _trans('common.Meeting Overview') }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span id="quickModalStatus" class="badge"></span>
                        <span id="quickModalType" class="badge bg-light text-dark border"></span>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted extra-small text-uppercase fw-semibold">{{ _trans('common.Date & Time') }}</div>
                        <div class="fw-bold text-dark fs-6" id="quickModalTime"></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted extra-small text-uppercase fw-semibold">{{ _trans('common.Location / Platform') }}</div>
                        <div class="text-dark" id="quickModalLocation"></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted extra-small text-uppercase fw-semibold">{{ _trans('common.Organizer') }}</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <img id="quickModalAvatar" src="" class="rounded-circle object-fit-cover shadow-sm" width="28" height="28">
                            <span class="text-dark fw-semibold" id="quickModalOrganizer"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Close') }}</button>
                    <a href="#" id="quickModalLink" class="btn btn-primary">{{ _trans('common.Open Details & Minutes') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.fc .fc-toolbar-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}
.fc .fc-button-primary {
    background-color: #ffffff;
    border-color: #e5e7eb;
    color: #4b5563;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    box-shadow: none;
    transition: all 0.2s ease;
}
.fc .fc-button-primary:hover,
.fc .fc-button-primary:focus {
    background-color: #f3f4f6;
    border-color: #d1d5db;
    color: #111827;
    box-shadow: none;
}
.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
    background-color: #4f46e5;
    border-color: #4f46e5;
    color: #ffffff;
}
.fc-theme-standard td, .fc-theme-standard th {
    border-color: #f3f4f6;
}
.fc-theme-standard .fc-scrollgrid {
    border-color: #f3f4f6;
    border-radius: 0.75rem;
    overflow: hidden;
}
.fc .fc-col-header-cell {
    background-color: #f9fafb;
    padding: 0.6rem 0;
    font-weight: 600;
    color: #4b5563;
    font-size: 0.85rem;
}
.fc-daygrid-event {
    border-radius: 0.375rem;
    padding: 2px 6px;
    font-size: 0.8rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.fc-event-time {
    font-weight: 600;
}
.fc-daygrid-day:hover {
    background-color: #fafafa;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/admin/js/fullcalendar.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('meetingCalendar');
    if (calendarEl) {
        var eventsData = @json($events ?? []);

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            navLinks: true,
            editable: false,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            events: eventsData,
            select: function(arg) {
                @can('meeting.create')
                    var selectedDate = arg.startStr.split('T')[0];
                    window.location.href = "{{ route('meetings.create') }}?date=" + selectedDate;
                @endcan
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                var props = info.event.extendedProps;
                
                $('#quickModalTitle span').text(info.event.title);
                $('#quickModalStatus').text(props.status).attr('class', 'badge ' + (props.statusClass || 'bg-primary'));
                $('#quickModalType').text(props.type);
                $('#quickModalTime').text(info.event.start.toLocaleDateString() + ' (' + props.timeRange + ')');
                $('#quickModalLocation').text(props.location);
                $('#quickModalOrganizer').text(props.organizer);
                if (props.organizerAvatar) {
                    $('#quickModalAvatar').attr('src', props.organizerAvatar).show();
                } else {
                    $('#quickModalAvatar').hide();
                }
                $('#quickModalLink').attr('href', info.event.url);

                var modal = new bootstrap.Modal(document.getElementById('eventQuickModal'));
                modal.show();
            }
        });

        calendar.render();
    }
});
</script>
@endpush
