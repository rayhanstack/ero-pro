@extends('admin.layouts.app')

@section('title', _trans('common.My Leaves'))

@section('content')
    <div class="container-fluid py-3">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ _trans('common.Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ _trans('common.My Leaves') }}</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">{{ _trans('common.My Leaves') }}</h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Year Filter Dropdown -->
                <form method="GET" action="{{ route('leaves.my') }}" class="d-inline-block">
                    <select name="year" class="form-select form-select-sm border-secondary-subtle rounded-3" onchange="this.form.submit()">
                        @for ($y = date('Y') + 1; $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>

                @can('leave.create')
                    <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1.5 rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ _trans('common.Apply Leave') }}</span>
                    </button>
                @endcan
            </div>
        </div>

        <!-- Leave Balance Cards -->
        <div class="row g-3 mb-4">
            @forelse ($balances as $balance)
                @php
                    $type = $balance->leaveType;
                    $total = $balance->total_available;
                    $used = $balance->used;
                    $remaining = $balance->remaining;
                    $percentage = $total > 0 ? min(100, round(($used / $total) * 100)) : 0;
                    $color = $type?->color ?? '#4f46e5';
                @endphp
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white hover-shadow-sm transition-all">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge rounded-pill px-2 py-1 text-white small" style="background-color: {{ $color }}">
                                        {{ $type?->code ?? 'LEAVE' }}
                                    </span>
                                    <h6 class="fw-bold mb-0 text-dark text-truncate" style="max-width: 130px;" title="{{ $type?->name }}">
                                        {{ $type?->name ?? _trans('common.Leave') }}
                                    </h6>
                                </div>
                                <span class="badge {{ $type?->is_paid ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill small" style="font-size: 11px;">
                                    {{ $type?->is_paid ? _trans('common.Paid') : _trans('common.Unpaid') }}
                                </span>
                            </div>

                            <div class="d-flex align-items-baseline justify-content-between my-2">
                                <div>
                                    <span class="display-6 fw-bold text-dark">{{ (float) $remaining }}</span>
                                    <span class="text-muted small">/ {{ (float) $total }} {{ _trans('common.Days Left') }}</span>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-light text-muted border small">{{ (float) $used }} {{ _trans('common.Used') }}</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="progress" style="height: 6px;" role="progressbar" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar rounded-pill" style="width: {{ $percentage }}%; background-color: {{ $color }};"></div>
                            </div>

                            @if ($balance->carried > 0)
                                <div class="mt-2 pt-1 border-top d-flex justify-content-between text-muted" style="font-size: 11px;">
                                    <span>{{ _trans('common.Carried Forward') }}:</span>
                                    <span class="fw-semibold text-dark">{{ (float) $balance->carried }} {{ _trans('common.Days') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info border-0 rounded-4 shadow-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i>{{ _trans('common.No leave types configured for this year.') }}
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Leave Requests History Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span>{{ _trans('common.My Leave History') }} ({{ $year }})</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-3">{{ _trans('common.Leave Type') }}</th>
                                <th>{{ _trans('common.Period') }}</th>
                                <th>{{ _trans('common.Days') }}</th>
                                <th>{{ _trans('common.Reason') }}</th>
                                <th>{{ _trans('common.Attachment') }}</th>
                                <th>{{ _trans('common.Status') }}</th>
                                <th>{{ _trans('common.Approver / Remarks') }}</th>
                                <th class="text-end pe-3">{{ _trans('common.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $request)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge rounded-pill px-2 py-1 text-white small" style="background-color: {{ $request->leaveType?->color ?? '#4f46e5' }}">
                                                {{ $request->leaveType?->code ?? 'LV' }}
                                            </span>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $request->leaveType?->name ?? '-' }}</div>
                                                <div class="text-muted small" style="font-size: 11px;">
                                                    {{ _trans('common.Applied on') }} {{ $request->created_at->format('M d, Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $request->from_date->format('d M, Y') }}
                                            @if ($request->from_date->format('Y-m-d') !== $request->to_date->format('Y-m-d'))
                                                <span class="text-muted fw-normal">→</span> {{ $request->to_date->format('d M, Y') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            {{ (float) $request->days }} {{ (float) $request->days > 1 ? _trans('common.Days') : _trans('common.Day') }}
                                        </span>
                                        @if ($request->half_day)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small ms-1" style="font-size: 10px;">
                                                {{ $request->half_day_type === 'second_half' ? _trans('common.2nd Half') : _trans('common.1st Half') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $request->reason }}">
                                            {{ $request->reason }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($request->attachment)
                                            <a href="{{ asset($request->attachment) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 11px;">
                                                <i class="bi bi-paperclip me-1"></i>{{ _trans('common.View') }}
                                            </a>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $request->status->badgeClass() }} rounded-pill px-2.5 py-1">
                                            {{ $request->status->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($request->approver)
                                            <div class="small fw-semibold text-dark">{{ $request->approver->name }}</div>
                                        @endif
                                        @if ($request->remark)
                                            <div class="text-muted small fst-italic" style="font-size: 11px;">
                                                "{{ $request->remark }}"
                                            </div>
                                        @elseif (! $request->approver)
                                            <span class="text-muted small">{{ _trans('common.Pending review') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        @if ($request->status === \App\Enums\LeaveRequestStatusEnum::PENDING)
                                            <form method="POST" action="{{ route('leaves.cancel', $request) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to cancel this leave request?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="{{ _trans('common.Cancel Request') }}">
                                                    <i class="bi bi-x-circle me-1"></i>{{ _trans('common.Cancel') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        {{ _trans('common.No leave requests found for this year.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($requests->hasPages())
                <div class="card-footer bg-transparent border-0 py-3">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Apply Leave Modal -->
    <div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-labelledby="applyLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form method="POST" action="{{ route('leaves.apply') }}" enctype="multipart/form-data" id="applyLeaveForm">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark" id="applyLeaveModalLabel">
                            <i class="bi bi-calendar-plus text-primary me-2"></i>{{ _trans('common.Apply For Leave') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="row g-3">
                            <!-- Leave Type -->
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Leave Type') }} <span class="text-danger">*</span></label>
                                <select name="leave_type_id" id="applyLeaveTypeId" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                                    <option value="">-- {{ _trans('common.Select Leave Type') }} --</option>
                                    @foreach ($leaveTypes as $ltype)
                                        @php
                                            $b = $balances->firstWhere('leave_type_id', $ltype->id);
                                            $rem = $b ? $b->remaining : $ltype->days_per_year;
                                        @endphp
                                        <option value="{{ $ltype->id }}" data-remaining="{{ $rem }}" {{ old('leave_type_id') == $ltype->id ? 'selected' : '' }}>
                                            {{ $ltype->name }} ({{ $ltype->code }}) — {{ (float) $rem }} {{ _trans('common.days available') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('leave_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Half Day Checkbox -->
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="half_day" id="applyHalfDay" value="1" {{ old('half_day') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="applyHalfDay">
                                        {{ _trans('common.Apply for Half Day') }}
                                    </label>
                                </div>
                            </div>

                            <!-- Half Day Type -->
                            <div class="col-md-6" id="halfDayTypeContainer" style="{{ old('half_day') ? '' : 'display: none;' }}">
                                <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Half Day Session') }}</label>
                                <select name="half_day_type" class="form-select">
                                    <option value="first_half" {{ old('half_day_type') === 'first_half' ? 'selected' : '' }}>{{ _trans('common.First Half (Morning)') }}</option>
                                    <option value="second_half" {{ old('half_day_type') === 'second_half' ? 'selected' : '' }}>{{ _trans('common.Second Half (Afternoon)') }}</option>
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div class="col-md-6" id="fromDateContainer">
                                <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.From Date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="from_date" id="applyFromDate" class="form-control @error('from_date') is-invalid @enderror" value="{{ old('from_date', date('Y-m-d')) }}" required>
                                @error('from_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="toDateContainer" style="{{ old('half_day') ? 'display: none;' : '' }}">
                                <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.To Date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="to_date" id="applyToDate" class="form-control @error('to_date') is-invalid @enderror" value="{{ old('to_date', date('Y-m-d')) }}">
                                @error('to_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Live Working Days Calculation Banner -->
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between" id="workingDaysBanner">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar-check text-primary fs-5"></i>
                                        <div>
                                            <div class="fw-bold text-dark small">{{ _trans('common.Calculated Working Days') }}</div>
                                            <div class="text-muted small" style="font-size: 11px;">{{ _trans('common.Excludes configured weekends & public holidays') }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary fs-6 px-3 py-1.5" id="calculatedDaysBadge">1.0 Day</span>
                                    </div>
                                </div>
                                <div class="text-danger small mt-1" id="balanceWarningText" style="display: none;">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ _trans('common.Insufficient leave balance for the requested duration.') }}
                                </div>
                            </div>

                            <!-- Reason -->
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Reason') }} <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="3" placeholder="{{ _trans('common.State the reason for your leave request...') }}" required>{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Attachment -->
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Attachment (Optional)') }}</label>
                                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                <div class="form-text small">{{ _trans('common.Medical certificate or supporting document (Max: 5MB)') }}</div>
                                @error('attachment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">{{ _trans('common.Close') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm" id="submitLeaveBtn">
                            <i class="bi bi-send me-1"></i>{{ _trans('common.Submit Request') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const calculateDaysUrl = "{{ route('leaves.calculate-days') }}";

        function updateLiveDays() {
            const leaveTypeId = $('#applyLeaveTypeId').val();
            const fromDate = $('#applyFromDate').val();
            const toDate = $('#applyToDate').val();
            const halfDay = $('#applyHalfDay').is(':checked') ? 1 : 0;

            if (!fromDate) return;

            $.ajax({
                url: calculateDaysUrl,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    leave_type_id: leaveTypeId,
                    from_date: fromDate,
                    to_date: toDate,
                    half_day: halfDay
                },
                success: function(res) {
                    const days = parseFloat(res.days || 0);
                    const unit = days === 1 ? "{{ _trans('common.Day') }}" : "{{ _trans('common.Days') }}";
                    $('#calculatedDaysBadge').text(days + ' ' + unit);

                    if (!res.has_balance && days > 0) {
                        $('#balanceWarningText').show();
                        $('#submitLeaveBtn').prop('disabled', true);
                    } else {
                        $('#balanceWarningText').hide();
                        $('#submitLeaveBtn').prop('disabled', days <= 0);
                    }
                }
            });
        }

        $('#applyHalfDay').on('change', function() {
            if ($(this).is(':checked')) {
                $('#halfDayTypeContainer').slideDown();
                $('#toDateContainer').slideUp();
            } else {
                $('#halfDayTypeContainer').slideUp();
                $('#toDateContainer').slideDown();
            }
            updateLiveDays();
        });

        $('#applyLeaveTypeId, #applyFromDate, #applyToDate').on('change', function() {
            updateLiveDays();
        });

        // Trigger on initial modal open
        $('#applyLeaveModal').on('shown.bs.modal', function () {
            updateLiveDays();
        });
    });
</script>
@endpush
