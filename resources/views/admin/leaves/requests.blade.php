@extends('admin.layouts.app')

@section('title', _trans('common.Leave Requests'))

@section('content')
    <div class="container-fluid py-3">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ _trans('common.Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leaves.my') }}" class="text-decoration-none">{{ _trans('common.Leaves') }}</a></li>
                        <li class="breadcrumb-item active">{{ _trans('common.Leave Requests') }}</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">{{ _trans('common.Leave Requests Approval Queue') }}</h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('leaves.calendar') }}" class="btn btn-outline-primary btn-sm rounded-3 d-flex align-items-center gap-1.5">
                    <i class="bi bi-calendar3"></i>
                    <span>{{ _trans('common.Leave Calendar') }}</span>
                </a>
                <a href="{{ route('leaves.balances') }}" class="btn btn-light btn-sm border rounded-3 d-flex align-items-center gap-1.5">
                    <i class="bi bi-pie-chart"></i>
                    <span>{{ _trans('common.Balances Report') }}</span>
                </a>
            </div>
        </div>

        <!-- Summary KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">{{ _trans('common.Total Requests') }}</div>
                            <div class="display-6 fw-bold text-dark mt-1">{{ $stats['total'] }}</div>
                        </div>
                        <div class="avatar-lg rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-inbox fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">{{ _trans('common.Pending Approval') }}</div>
                            <div class="display-6 fw-bold text-warning mt-1">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="avatar-lg rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">{{ _trans('common.Approved') }}</div>
                            <div class="display-6 fw-bold text-success mt-1">{{ $stats['approved'] }}</div>
                        </div>
                        <div class="avatar-lg rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">{{ _trans('common.Rejected') }}</div>
                            <div class="display-6 fw-bold text-danger mt-1">{{ $stats['rejected'] }}</div>
                        </div>
                        <div class="avatar-lg rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-x-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search Toolbar -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('leaves.requests') }}" class="row g-2 align-items-center">
                    <!-- Status Filter Tabs / Select -->
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm rounded-3">
                            <option value="">{{ _trans('common.All Statuses') }}</option>
                            @foreach (\App\Enums\LeaveRequestStatusEnum::cases() as $st)
                                <option value="{{ $st->value }}" {{ ($filters['status'] ?? '') === $st->value ? 'selected' : '' }}>
                                    {{ $st->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department Filter -->
                    <div class="col-md-2">
                        <select name="department_id" class="form-select form-select-sm rounded-3">
                            <option value="">{{ _trans('common.All Departments') }}</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Leave Type Filter -->
                    <div class="col-md-2">
                        <select name="leave_type_id" class="form-select form-select-sm rounded-3">
                            <option value="">{{ _trans('common.All Leave Types') }}</option>
                            @foreach ($leaveTypes as $lt)
                                <option value="{{ $lt->id }}" {{ ($filters['leave_type_id'] ?? '') == $lt->id ? 'selected' : '' }}>
                                    {{ $lt->name }} ({{ $lt->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Employee / Reason -->
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="{{ _trans('common.Search employee, ID, reason...') }}" value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm rounded-3 flex-grow-1">
                            <i class="bi bi-filter me-1"></i>{{ _trans('common.Filter') }}
                        </button>
                        <a href="{{ route('leaves.requests') }}" class="btn btn-light btn-sm border rounded-3" title="{{ _trans('common.Reset Filters') }}">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Requests Queue Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-3">{{ _trans('common.Employee') }}</th>
                                <th>{{ _trans('common.Leave Type') }}</th>
                                <th>{{ _trans('common.Duration') }}</th>
                                <th>{{ _trans('common.Days') }}</th>
                                <th>{{ _trans('common.Reason') }}</th>
                                <th>{{ _trans('common.Attachment') }}</th>
                                <th>{{ _trans('common.Status') }}</th>
                                <th>{{ _trans('common.Approver / Remarks') }}</th>
                                <th class="text-end pe-3">{{ _trans('common.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $req)
                                <tr>
                                    <!-- Employee Info -->
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $req->employee?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($req->employee?->name ?? 'User') . '&background=4f46e5&color=fff' }}" class="rounded-circle object-fit-cover flex-shrink-0" width="36" height="36" alt="{{ $req->employee?->name }}">
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $req->employee?->name ?? '-' }}</div>
                                                <div class="text-muted small" style="font-size: 11px;">
                                                    {{ $req->employee?->emp_code ?? $req->employee?->email }} • {{ $req->employee?->department?->name ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Leave Type -->
                                    <td>
                                        <span class="badge rounded-pill px-2.5 py-1 text-white" style="background-color: {{ $req->leaveType?->color ?? '#4f46e5' }}">
                                            {{ $req->leaveType?->name ?? '-' }}
                                        </span>
                                    </td>

                                    <!-- Duration -->
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $req->from_date->format('d M, Y') }}
                                            @if ($req->from_date->format('Y-m-d') !== $req->to_date->format('Y-m-d'))
                                                <span class="text-muted fw-normal">→</span> {{ $req->to_date->format('d M, Y') }}
                                            @endif
                                        </div>
                                        <div class="text-muted small" style="font-size: 11px;">
                                            {{ _trans('common.Applied') }} {{ $req->created_at->diffForHumans() }}
                                        </div>
                                    </td>

                                    <!-- Days -->
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            {{ (float) $req->days }} {{ (float) $req->days > 1 ? _trans('common.Days') : _trans('common.Day') }}
                                        </span>
                                        @if ($req->half_day)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small ms-1" style="font-size: 10px;">
                                                {{ $req->half_day_type === 'second_half' ? _trans('common.2nd Half') : _trans('common.1st Half') }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Reason -->
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 180px;" title="{{ $req->reason }}">
                                            {{ $req->reason }}
                                        </span>
                                    </td>

                                    <!-- Attachment -->
                                    <td>
                                        @if ($req->attachment)
                                            <a href="{{ asset($req->attachment) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 11px;">
                                                <i class="bi bi-paperclip me-1"></i>{{ _trans('common.View') }}
                                            </a>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <span class="badge {{ $req->status->badgeClass() }} rounded-pill px-2.5 py-1">
                                            {{ $req->status->label() }}
                                        </span>
                                    </td>

                                    <!-- Approver & Remark -->
                                    <td>
                                        @if ($req->approver)
                                            <div class="small fw-semibold text-dark">{{ $req->approver->name }}</div>
                                        @endif
                                        @if ($req->remark)
                                            <div class="text-muted small fst-italic" style="font-size: 11px;">
                                                "{{ $req->remark }}"
                                            </div>
                                        @elseif (! $req->approver)
                                            <span class="text-muted small">{{ _trans('common.Pending') }}</span>
                                        @endif
                                    </td>

                                    <!-- Action -->
                                    <td class="text-end pe-3">
                                        @if ($req->status === \App\Enums\LeaveRequestStatusEnum::PENDING)
                                            @can('leave.approve')
                                                <button type="button" class="btn btn-sm btn-primary rounded-3 px-2.5 py-1 d-inline-flex align-items-center gap-1 action-leave-btn"
                                                    data-id="{{ $req->id }}"
                                                    data-employee="{{ $req->employee?->name }}"
                                                    data-type="{{ $req->leaveType?->name }}"
                                                    data-duration="{{ $req->from_date->format('d M, Y') }} - {{ $req->to_date->format('d M, Y') }} ({{ (float) $req->days }} days)"
                                                    data-reason="{{ $req->reason }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#actionLeaveModal">
                                                    <i class="bi bi-pencil-square"></i>
                                                    <span>{{ _trans('common.Review') }}</span>
                                                </button>
                                            @endcan
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        {{ _trans('common.No leave requests found matching the filters.') }}
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

    <!-- Review / Action Modal -->
    <div class="modal fade" id="actionLeaveModal" tabindex="-1" aria-labelledby="actionLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form method="POST" action="" id="actionLeaveForm">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" id="actionTypeInput" value="approve">

                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark" id="actionLeaveModalLabel">
                            <i class="bi bi-shield-check text-primary me-2"></i>{{ _trans('common.Review Leave Request') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body py-3">
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="row g-2 small">
                                <div class="col-4 text-muted">{{ _trans('common.Employee') }}:</div>
                                <div class="col-8 fw-semibold text-dark" id="modalEmployeeName">-</div>

                                <div class="col-4 text-muted">{{ _trans('common.Leave Type') }}:</div>
                                <div class="col-8 fw-semibold text-dark" id="modalLeaveType">-</div>

                                <div class="col-4 text-muted">{{ _trans('common.Period') }}:</div>
                                <div class="col-8 fw-semibold text-dark" id="modalDuration">-</div>

                                <div class="col-4 text-muted">{{ _trans('common.Reason') }}:</div>
                                <div class="col-8 text-dark fst-italic" id="modalReason">-</div>
                            </div>
                        </div>

                        <!-- Remarks Input -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Approver Remarks / Reason (Optional)') }}</label>
                            <textarea name="remark" class="form-control" rows="3" placeholder="{{ _trans('common.Enter any notes or remarks regarding this decision...') }}"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                        <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-danger rounded-3 px-3" id="rejectBtn">
                                <i class="bi bi-x-circle me-1"></i>{{ _trans('common.Reject') }}
                            </button>
                            <button type="button" class="btn btn-success rounded-3 px-4 shadow-sm" id="approveBtn">
                                <i class="bi bi-check-circle me-1"></i>{{ _trans('common.Approve') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const actionBaseUrl = "{{ url('leaves/requests') }}";

        $('.action-leave-btn').on('click', function() {
            const id = $(this).data('id');
            const employee = $(this).data('employee');
            const type = $(this).data('type');
            const duration = $(this).data('duration');
            const reason = $(this).data('reason');

            $('#modalEmployeeName').text(employee);
            $('#modalLeaveType').text(type);
            $('#modalDuration').text(duration);
            $('#modalReason').text(reason);

            $('#actionLeaveForm').attr('action', actionBaseUrl + '/' + id + '/action');
        });

        $('#approveBtn').on('click', function() {
            $('#actionTypeInput').val('approve');
            $('#actionLeaveForm').submit();
        });

        $('#rejectBtn').on('click', function() {
            $('#actionTypeInput').val('reject');
            $('#actionLeaveForm').submit();
        });
    });
</script>
@endpush
