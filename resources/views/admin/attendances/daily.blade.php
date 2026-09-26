@extends('admin.layouts.app')

@section('title', _trans('common.Daily Attendance'))

@section('content')
    <div class="container-fluid py-3">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ _trans('common.Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('attendances.my') }}" class="text-decoration-none">{{ _trans('common.Attendance') }}</a></li>
                        <li class="breadcrumb-item active">{{ _trans('common.Daily Attendance') }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">{{ _trans('common.Daily Attendance') }}</h4>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill small">
                        {{ Carbon\Carbon::parse($date)->format('l, d F Y') }}
                    </span>
                    @if ($dailyData['is_holiday'])
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 small">{{ _trans('common.Holiday') }}</span>
                    @elseif ($dailyData['is_weekend'])
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-0.5 small">{{ _trans('common.Weekend') }}</span>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @can('attendance.manage')
                    <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1.5 rounded-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#manualAttendanceModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ _trans('common.Manual Attendance') }}</span>
                    </button>
                @endcan
                <a href="{{ route('attendances.monthly') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1.5 rounded-3">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span>{{ _trans('common.Monthly Sheet') }}</span>
                </a>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 text-center border-start border-primary border-3">
                    <span class="text-muted small d-block">{{ _trans('common.Total Staff') }}</span>
                    <h4 class="fw-bold text-dark mb-0 mt-1">{{ $dailyData['summary']['total'] }}</h4>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 text-center border-start border-success border-3">
                    <span class="text-muted small d-block">{{ _trans('common.Present') }}</span>
                    <h4 class="fw-bold text-success mb-0 mt-1">{{ $dailyData['summary']['present'] }}</h4>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 text-center border-start border-warning border-3">
                    <span class="text-muted small d-block">{{ _trans('common.Late Arrival') }}</span>
                    <h4 class="fw-bold text-warning-emphasis mb-0 mt-1">{{ $dailyData['summary']['late'] }}</h4>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 text-center border-start border-danger border-3">
                    <span class="text-muted small d-block">{{ _trans('common.Absent') }}</span>
                    <h4 class="fw-bold text-danger mb-0 mt-1">{{ $dailyData['summary']['absent'] }}</h4>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 text-center border-start border-info border-3">
                    <span class="text-muted small d-block">{{ _trans('common.On Leave') }}</span>
                    <h4 class="fw-bold text-info mb-0 mt-1">{{ $dailyData['summary']['leave'] }}</h4>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 text-center border-start border-success border-3">
                    <span class="text-muted small d-block">{{ _trans('common.Present Rate') }}</span>
                    <h4 class="fw-bold text-success mb-0 mt-1">{{ $dailyData['summary']['attendance_rate'] }}%</h4>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('attendances.daily') }}" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">{{ _trans('common.Select Date') }}</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="date" class="form-control rounded-start-3" value="{{ $date }}" required>
                            <a href="{{ route('attendances.daily', ['date' => Carbon\Carbon::today()->toDateString()]) }}" class="btn btn-outline-secondary" title="{{ _trans('common.Today') }}">
                                {{ _trans('common.Today') }}
                            </a>
                        </div>
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
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">{{ _trans('common.Search Employee') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 rounded-end-3" placeholder="{{ _trans('common.Search by name, code, email...') }}" value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex gap-2 align-items-end pt-3 pt-md-0">
                        <button type="submit" class="btn btn-primary btn-sm rounded-3 px-3 w-100">
                            <i class="bi bi-filter me-1"></i>{{ _trans('common.Filter') }}
                        </button>
                        @if ($date !== Carbon\Carbon::today()->toDateString() || $departmentId || $search)
                            <a href="{{ route('attendances.daily') }}" class="btn btn-light btn-sm border rounded-3 px-2" title="{{ _trans('common.Reset') }}">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendance Records Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">{{ _trans('common.Employee') }}</th>
                            <th>{{ _trans('common.Shift') }}</th>
                            <th>{{ _trans('common.Check In') }}</th>
                            <th>{{ _trans('common.Check Out') }}</th>
                            <th>{{ _trans('common.Duration') }}</th>
                            <th>{{ _trans('common.Late') }}</th>
                            <th>{{ _trans('common.Overtime') }}</th>
                            <th>{{ _trans('common.Status') }}</th>
                            <th>{{ _trans('common.Source') }}</th>
                            <th class="text-end pe-3">{{ _trans('common.Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dailyData['employees'] as $emp)
                            @php
                                $att = $emp->attendances->first();
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $emp->avatar_url }}" alt="{{ $emp->name }}" class="rounded-circle object-fit-cover me-2 flex-shrink-0" width="36" height="36">
                                        <div>
                                            <a href="{{ route('employees.show', $emp) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                                {{ $emp->name }}
                                            </a>
                                            <span class="text-muted small" style="font-size: 11px;">
                                                <span class="badge bg-light text-secondary border me-1 font-monospace">{{ $emp->emp_code }}</span>
                                                {{ $emp->department?->name ?? '-' }} • {{ $emp->designation?->name ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="small">
                                    @if ($emp->shift)
                                        <span class="fw-medium text-dark d-block">{{ $emp->shift->name }}</span>
                                        <span class="text-muted font-monospace" style="font-size: 11px;">
                                            {{ Carbon\Carbon::parse($emp->shift->start_time)->format('h:i A') }} - {{ Carbon\Carbon::parse($emp->shift->end_time)->format('h:i A') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small font-monospace">
                                    @if ($att && $att->check_in)
                                        <span class="badge bg-light text-dark border">{{ $att->check_in_time }}</span>
                                    @else
                                        <span class="text-muted">--:--</span>
                                    @endif
                                </td>
                                <td class="small font-monospace">
                                    @if ($att && $att->check_out)
                                        <span class="badge bg-light text-dark border">{{ $att->check_out_time }}</span>
                                    @else
                                        <span class="text-muted">--:--</span>
                                    @endif
                                </td>
                                <td class="small fw-semibold text-dark">
                                    {{ $att ? $att->work_duration_formatted : '-' }}
                                </td>
                                <td class="small">
                                    @if ($att && $att->late_minutes > 0)
                                        <span class="text-danger fw-semibold">{{ $att->late_duration_formatted }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if ($att && $att->overtime_minutes > 0)
                                        <span class="text-success fw-semibold">{{ $att->overtime_duration_formatted }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($att)
                                        <span class="{{ $att->status->badgeClass() }}">{{ $att->status->label() }}</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">{{ _trans('common.Not Marked') }}</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    @if ($att)
                                        <span class="badge bg-light text-dark border">{{ $att->source->label() }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @can('attendance.manage')
                                        <button type="button" class="btn btn-sm btn-light border rounded-3 px-2 py-1" onclick="openEditManualModal('{{ $emp->id }}', '{{ $emp->name }}', '{{ $date }}', '{{ $att ? $att->status->value : 'present' }}', '{{ $att && $att->check_in ? $att->check_in->format('H:i') : '' }}', '{{ $att && $att->check_out ? $att->check_out->format('H:i') : '' }}', '{{ $att ? addslashes($att->note ?? '') : '' }}', '{{ $att ? route('attendances.update', $att) : route('attendances.store') }}', '{{ $att ? 'PUT' : 'POST' }}')" title="{{ _trans('common.Edit Attendance') }}">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </button>
                                        @if ($att)
                                            <form method="POST" action="{{ route('attendances.destroy', $att) }}" class="d-inline-block" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this attendance record?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border rounded-3 px-2 py-1 text-danger" title="{{ _trans('common.Delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
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

    <!-- Manual Add / Edit Attendance Modal -->
    <div class="modal fade" id="manualAttendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST" id="manualAttendanceForm" action="{{ route('attendances.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="manualMethod" value="POST">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark" id="manualModalTitle">{{ _trans('common.Manual Attendance Entry') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="mb-3" id="employeeSelectWrapper">
                            <label class="form-label small fw-semibold text-dark">{{ _trans('common.Employee') }} <span class="text-danger">*</span></label>
                            <select name="employee_id" id="manualEmployeeId" class="form-select rounded-3" required>
                                <option value="">{{ _trans('common.Select Employee') }}</option>
                                @foreach ($dailyData['employees'] as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->emp_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="employeeNameDisplayWrapper">
                            <label class="form-label small fw-semibold text-dark">{{ _trans('common.Employee') }}</label>
                            <input type="text" id="manualEmployeeName" class="form-control rounded-3 bg-light" readonly>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-dark">{{ _trans('common.Date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="manualDate" class="form-control rounded-3" value="{{ $date }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-dark">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                                <select name="status" id="manualStatus" class="form-select rounded-3" required>
                                    @foreach ($statuses as $st)
                                        <option value="{{ $st->value }}">{{ $st->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-dark">{{ _trans('common.Check In Time') }}</label>
                                <input type="time" name="check_in" id="manualCheckIn" class="form-control rounded-3">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-dark">{{ _trans('common.Check Out Time') }}</label>
                                <input type="time" name="check_out" id="manualCheckOut" class="form-control rounded-3">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">{{ _trans('common.Note / Reason') }}</label>
                            <textarea name="note" id="manualNote" rows="2" class="form-control rounded-3" placeholder="{{ _trans('common.Optional notes regarding manual adjustment...') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">{{ _trans('common.Save Attendance') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openEditManualModal(empId, empName, date, status, checkIn, checkOut, note, formAction, method) {
        document.getElementById('manualAttendanceForm').action = formAction;
        document.getElementById('manualMethod').value = method;
        document.getElementById('manualEmployeeId').value = empId;
        document.getElementById('manualEmployeeName').value = empName;
        document.getElementById('manualDate').value = date;
        document.getElementById('manualStatus').value = status;
        document.getElementById('manualCheckIn').value = checkIn;
        document.getElementById('manualCheckOut').value = checkOut;
        document.getElementById('manualNote').value = note;

        if (method === 'PUT') {
            document.getElementById('manualModalTitle').textContent = '{{ _trans('common.Edit Attendance Record') }}';
            document.getElementById('employeeSelectWrapper').classList.add('d-none');
            document.getElementById('employeeNameDisplayWrapper').classList.remove('d-none');
        } else {
            document.getElementById('manualModalTitle').textContent = '{{ _trans('common.Manual Attendance Entry') }}';
            document.getElementById('employeeSelectWrapper').classList.remove('d-none');
            document.getElementById('employeeNameDisplayWrapper').classList.add('d-none');
        }

        const modal = new bootstrap.Modal(document.getElementById('manualAttendanceModal'));
        modal.show();
    }
</script>
@endpush
