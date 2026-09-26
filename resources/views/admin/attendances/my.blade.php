@extends('admin.layouts.app')

@section('title', _trans('common.My Attendance'))

@section('content')
    <div class="container-fluid py-3">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ _trans('common.Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ _trans('common.My Attendance') }}</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">{{ _trans('common.My Attendance') }}</h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1.5 rounded-3" data-bs-toggle="modal" data-bs-target="#regularizationModal">
                    <i class="bi bi-clock-history"></i>
                    <span>{{ _trans('common.Request Regularization') }}</span>
                </button>
                <a href="{{ route('attendances.regularizations') }}" class="btn btn-light btn-sm border d-flex align-items-center gap-1.5 rounded-3">
                    <i class="bi bi-list-check"></i>
                    <span>{{ _trans('common.My Requests') }}</span>
                    @if ($pendingRegularizations > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $pendingRegularizations }}</span>
                    @endif
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Today's Punch Widget -->
            <div class="col-lg-5 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill small">
                            <i class="bi bi-calendar-check me-1"></i>{{ Carbon\Carbon::today()->format('l, d M Y') }}
                        </span>
                        @if ($todayAttendance)
                            {!! $todayAttendance->status->badgeClass() ? '<span class="' . $todayAttendance->status->badgeClass() . '">' . $todayAttendance->status->label() . '</span>' : '' !!}
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill small">{{ _trans('common.Not Checked In') }}</span>
                        @endif
                    </div>
                    <div class="card-body text-center py-4 d-flex flex-column justify-content-center align-items-center">
                        <!-- Digital Live Clock -->
                        <div class="display-6 fw-bold font-monospace text-dark mb-1" id="liveClock">
                            {{ Carbon\Carbon::now()->format('h:i:s A') }}
                        </div>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-geo-alt me-1"></i>{{ _trans('common.IP') }}: <span class="font-monospace text-dark fw-medium">{{ request()->ip() }}</span>
                            @if ($user->shift)
                                <span class="mx-1">•</span>
                                <i class="bi bi-clock me-1"></i>{{ $user->shift->name }} ({{ Carbon\Carbon::parse($user->shift->start_time)->format('h:i A') }} - {{ Carbon\Carbon::parse($user->shift->end_time)->format('h:i A') }})
                            @endif
                        </p>

                        <!-- Big Punch Button -->
                        @php
                            $isCheckedIn = $todayAttendance && $todayAttendance->check_in !== null;
                            $isCheckedOut = $todayAttendance && $todayAttendance->check_out !== null;
                        @endphp

                        @if (! $isCheckedIn)
                            <form method="POST" action="{{ route('attendances.punch') }}" class="w-100 px-3">
                                @csrf
                                <input type="hidden" name="type" value="in">
                                <button type="submit" class="btn btn-success btn-lg w-100 py-3 rounded-4 shadow-sm d-flex flex-column align-items-center justify-content-center gap-1">
                                    <i class="bi bi-box-arrow-in-right fs-2"></i>
                                    <span class="fw-bold fs-5">{{ _trans('common.Punch In') }}</span>
                                    <span class="small opacity-75 fw-normal">{{ _trans('common.Click to mark arrival') }}</span>
                                </button>
                            </form>
                        @elseif ($isCheckedIn && ! $isCheckedOut)
                            <form method="POST" action="{{ route('attendances.punch') }}" class="w-100 px-3">
                                @csrf
                                <input type="hidden" name="type" value="out">
                                <button type="submit" class="btn btn-warning btn-lg w-100 py-3 rounded-4 shadow-sm text-dark d-flex flex-column align-items-center justify-content-center gap-1">
                                    <i class="bi bi-box-arrow-right fs-2"></i>
                                    <span class="fw-bold fs-5">{{ _trans('common.Punch Out') }}</span>
                                    <span class="small opacity-75 fw-normal">{{ _trans('common.In at') }} {{ $todayAttendance->check_in_time }}</span>
                                </button>
                            </form>
                        @else
                            <div class="bg-light-subtle border border-success-subtle rounded-4 p-3 w-100 text-center">
                                <div class="text-success mb-1"><i class="bi bi-check-circle-fill fs-2"></i></div>
                                <h6 class="fw-bold text-dark mb-1">{{ _trans('common.Day Completed') }}</h6>
                                <p class="text-muted small mb-0">{{ _trans('common.Total Worked') }}: <strong class="text-success">{{ $todayAttendance->work_duration_formatted }}</strong></p>
                            </div>
                        @endif

                        <!-- Punch Timestamps -->
                        <div class="row w-100 g-2 mt-3 pt-3 border-top text-start">
                            <div class="col-6">
                                <div class="p-2.5 bg-light rounded-3 text-center">
                                    <div class="text-muted small" style="font-size: 11px;">{{ _trans('common.Punch In') }}</div>
                                    <div class="fw-bold text-dark small font-monospace">{{ $todayAttendance?->check_in_time ?? '--:--' }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2.5 bg-light rounded-3 text-center">
                                    <div class="text-muted small" style="font-size: 11px;">{{ _trans('common.Punch Out') }}</div>
                                    <div class="fw-bold text-dark small font-monospace">{{ $todayAttendance?->check_out_time ?? '--:--' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Month Summary Stats -->
            <div class="col-lg-7 col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <h6 class="fw-bold text-dark mb-0">{{ _trans('common.Monthly Overview') }} ({{ $monthData['month_name'] }})</h6>
                        <form method="GET" action="{{ route('attendances.my') }}" class="d-flex align-items-center gap-2 m-0">
                            <select name="month" class="form-select form-select-sm rounded-3" style="width: 120px;">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                            <select name="year" class="form-select form-select-sm rounded-3" style="width: 90px;">
                                @for ($y = Carbon\Carbon::now()->year; $y >= Carbon\Carbon::now()->year - 3; $y--)
                                    <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary rounded-3 px-2.5">
                                <i class="bi bi-funnel"></i>
                            </button>
                        </form>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center border-start border-primary border-3">
                                    <span class="text-muted small d-block">{{ _trans('common.Working Days') }}</span>
                                    <h4 class="fw-bold text-primary mb-0 mt-1">{{ $monthData['stats']['working_days'] }}</h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center border-start border-success border-3">
                                    <span class="text-muted small d-block">{{ _trans('common.Present Days') }}</span>
                                    <h4 class="fw-bold text-success mb-0 mt-1">{{ $monthData['stats']['present'] }}</h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center border-start border-warning border-3">
                                    <span class="text-muted small d-block">{{ _trans('common.Late Days') }}</span>
                                    <h4 class="fw-bold text-warning-emphasis mb-0 mt-1">{{ $monthData['stats']['late'] }}</h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center border-start border-danger border-3">
                                    <span class="text-muted small d-block">{{ _trans('common.Absent Days') }}</span>
                                    <h4 class="fw-bold text-danger mb-0 mt-1">{{ $monthData['stats']['absent'] }}</h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center border-start border-info border-3">
                                    <span class="text-muted small d-block">{{ _trans('common.Leaves Taken') }}</span>
                                    <h4 class="fw-bold text-info mb-0 mt-1">{{ $monthData['stats']['leave'] }}</h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center border-start border-purple border-3">
                                    <span class="text-muted small d-block">{{ _trans('common.Half Days') }}</span>
                                    <h4 class="fw-bold text-purple mb-0 mt-1">{{ $monthData['stats']['half_day'] }}</h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center border-start border-dark border-3">
                                    <span class="text-muted small d-block">{{ _trans('common.Total Hours') }}</span>
                                    <h4 class="fw-bold text-dark mb-0 mt-1">{{ $monthData['stats']['work_hours'] }}h</h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center border-start border-success border-3">
                                    <span class="text-muted small d-block">{{ _trans('common.Overtime Hours') }}</span>
                                    <h4 class="fw-bold text-success mb-0 mt-1">{{ $monthData['stats']['overtime_hours'] }}h</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Logs Table for Current Month -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history me-1.5 text-primary"></i>{{ _trans('common.Attendance Logs') }} ({{ $monthData['month_name'] }})</h6>
                <span class="text-muted small">{{ $monthData['attendances']->count() }} {{ _trans('common.records') }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">{{ _trans('common.Date') }}</th>
                            <th>{{ _trans('common.Check In') }}</th>
                            <th>{{ _trans('common.Check Out') }}</th>
                            <th>{{ _trans('common.Work Duration') }}</th>
                            <th>{{ _trans('common.Late') }}</th>
                            <th>{{ _trans('common.Overtime') }}</th>
                            <th>{{ _trans('common.Status') }}</th>
                            <th>{{ _trans('common.Source / Note') }}</th>
                            <th class="text-end pe-3">{{ _trans('common.Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($monthData['attendances'] as $record)
                            <tr>
                                <td class="ps-3 fw-medium text-dark">
                                    {{ $record->date->format('d M Y') }}
                                    <span class="text-muted small d-block" style="font-size: 11px;">{{ $record->date->format('l') }}</span>
                                </td>
                                <td class="font-monospace small">
                                    @if ($record->check_in)
                                        <span class="badge bg-light text-dark border">{{ $record->check_in_time }}</span>
                                    @else
                                        <span class="text-muted">--:--</span>
                                    @endif
                                </td>
                                <td class="font-monospace small">
                                    @if ($record->check_out)
                                        <span class="badge bg-light text-dark border">{{ $record->check_out_time }}</span>
                                    @else
                                        <span class="text-muted">--:--</span>
                                    @endif
                                </td>
                                <td class="small fw-semibold text-dark">{{ $record->work_duration_formatted }}</td>
                                <td class="small">
                                    @if ($record->late_minutes > 0)
                                        <span class="text-danger fw-semibold">{{ $record->late_duration_formatted }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if ($record->overtime_minutes > 0)
                                        <span class="text-success fw-semibold">{{ $record->overtime_duration_formatted }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $record->status->badgeClass() }}">{{ $record->status->label() }}</span>
                                </td>
                                <td class="small text-muted">
                                    <span class="badge bg-light text-dark border me-1">{{ $record->source->label() }}</span>
                                    {{ $record->note ?? '-' }}
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1" onclick="openRegularizeModal('{{ $record->date->format('Y-m-d') }}', '{{ $record->check_in ? $record->check_in->format('H:i') : '' }}', '{{ $record->check_out ? $record->check_out->format('H:i') : '' }}')">
                                        <i class="bi bi-pencil-square me-1"></i>{{ _trans('common.Regularize') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-1 text-secondary"></i>
                                    {{ _trans('common.No attendance records found for this month.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Regularization Request Modal -->
    <div class="modal fade" id="regularizationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST" action="{{ route('attendances.regularizations.store') }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">{{ _trans('common.Request Attendance Regularization') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">{{ _trans('common.Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="regDate" class="form-control rounded-3" max="{{ Carbon\Carbon::today()->toDateString() }}" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-dark">{{ _trans('common.Requested Check-In') }}</label>
                                <input type="time" name="requested_in" id="regCheckIn" class="form-control rounded-3">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-dark">{{ _trans('common.Requested Check-Out') }}</label>
                                <input type="time" name="requested_out" id="regCheckOut" class="form-control rounded-3">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">{{ _trans('common.Reason') }} <span class="text-danger">*</span></label>
                            <textarea name="reason" rows="3" class="form-control rounded-3" placeholder="{{ _trans('common.Explain reason (e.g., forgot to punch, system issue, field work)...') }}" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">{{ _trans('common.Submit Request') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Live Digital Clock
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.textContent = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
        }
    }
    setInterval(updateClock, 1000);

    function openRegularizeModal(date, checkIn, checkOut) {
        document.getElementById('regDate').value = date;
        document.getElementById('regCheckIn').value = checkIn;
        document.getElementById('regCheckOut').value = checkOut;
        const modal = new bootstrap.Modal(document.getElementById('regularizationModal'));
        modal.show();
    }
</script>
@endpush
