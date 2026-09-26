@extends('admin.layouts.app')

@section('title', _trans('common.Attendance Regularizations'))

@section('content')
    <div class="container-fluid py-3">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ _trans('common.Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('attendances.my') }}" class="text-decoration-none">{{ _trans('common.Attendance') }}</a></li>
                        <li class="breadcrumb-item active">{{ _trans('common.Regularizations') }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">{{ _trans('common.Attendance Regularizations') }}</h4>
                    @if ($pendingCount > 0 && $isHrOrAdmin)
                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill small">
                            {{ $pendingCount }} {{ _trans('common.Pending Approval') }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1.5 rounded-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#newRegularizationModal">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.New Request') }}</span>
                </button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-2">
                <ul class="nav nav-pills gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'all' ? 'active' : '' }} rounded-3 py-1.5 px-3 small fw-semibold" href="{{ route('attendances.regularizations', ['status' => 'all']) }}">
                            {{ _trans('common.All Requests') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'pending' ? 'active' : '' }} rounded-3 py-1.5 px-3 small fw-semibold" href="{{ route('attendances.regularizations', ['status' => 'pending']) }}">
                            {{ _trans('common.Pending') }}
                            @if ($pendingCount > 0)
                                <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'approved' ? 'active' : '' }} rounded-3 py-1.5 px-3 small fw-semibold" href="{{ route('attendances.regularizations', ['status' => 'approved']) }}">
                            {{ _trans('common.Approved') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'rejected' ? 'active' : '' }} rounded-3 py-1.5 px-3 small fw-semibold" href="{{ route('attendances.regularizations', ['status' => 'rejected']) }}">
                            {{ _trans('common.Rejected') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Regularization Requests Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">{{ _trans('common.Employee') }}</th>
                            <th>{{ _trans('common.Date') }}</th>
                            <th>{{ _trans('common.Requested In') }}</th>
                            <th>{{ _trans('common.Requested Out') }}</th>
                            <th>{{ _trans('common.Reason') }}</th>
                            <th>{{ _trans('common.Status') }}</th>
                            <th>{{ _trans('common.Approver / Decision Note') }}</th>
                            <th class="text-end pe-3">{{ _trans('common.Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($regularizations as $reg)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $reg->employee->avatar_url }}" alt="{{ $reg->employee->name }}" class="rounded-circle object-fit-cover me-2 flex-shrink-0" width="32" height="32">
                                        <div>
                                            <a href="{{ route('employees.show', $reg->employee) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                                {{ $reg->employee->name }}
                                            </a>
                                            <span class="text-muted small" style="font-size: 11px;">
                                                {{ $reg->employee->emp_code }} • {{ $reg->employee->department?->name ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-medium text-dark small">
                                    {{ $reg->date->format('d M Y') }}
                                    <span class="text-muted small d-block" style="font-size: 11px;">{{ $reg->date->format('l') }}</span>
                                </td>
                                <td class="small font-monospace">
                                    @if ($reg->requested_in)
                                        <span class="badge bg-light text-dark border">{{ $reg->requested_in->format('h:i A') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small font-monospace">
                                    @if ($reg->requested_out)
                                        <span class="badge bg-light text-dark border">{{ $reg->requested_out->format('h:i A') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small text-dark" style="max-width: 250px;">
                                    {{ $reg->reason }}
                                </td>
                                <td>
                                    <span class="{{ $reg->status->badgeClass() }}">{{ $reg->status->label() }}</span>
                                </td>
                                <td class="small">
                                    @if ($reg->approver)
                                        <span class="fw-medium text-dark d-block">{{ $reg->approver->name }}</span>
                                    @endif
                                    @if ($reg->admin_note)
                                        <span class="text-muted small fst-italic">"{{ $reg->admin_note }}"</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if ($isHrOrAdmin && $reg->status === \App\Enums\RegularizationStatusEnum::PENDING)
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-success rounded-start-3 px-2 py-1" onclick="openActionModal('{{ route('attendances.regularizations.action', $reg) }}', 'approve', '{{ $reg->employee->name }}', '{{ $reg->date->format('d M Y') }}')" title="{{ _trans('common.Approve') }}">
                                                <i class="bi bi-check-lg me-1"></i>{{ _trans('common.Approve') }}
                                            </button>
                                            <button type="button" class="btn btn-outline-danger rounded-end-3 px-2 py-1" onclick="openActionModal('{{ route('attendances.regularizations.action', $reg) }}', 'reject', '{{ $reg->employee->name }}', '{{ $reg->date->format('d M Y') }}')" title="{{ _trans('common.Reject') }}">
                                                <i class="bi bi-x-lg me-1"></i>{{ _trans('common.Reject') }}
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-1 text-secondary"></i>
                                    {{ _trans('common.No regularization requests found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($regularizations->hasPages())
                <div class="card-footer bg-transparent border-0 pt-3">
                    {{ $regularizations->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- New Regularization Request Modal -->
    <div class="modal fade" id="newRegularizationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST" action="{{ route('attendances.regularizations.store') }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">{{ _trans('common.Submit Attendance Regularization') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">{{ _trans('common.Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control rounded-3" max="{{ Carbon\Carbon::today()->toDateString() }}" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-dark">{{ _trans('common.Requested Check-In') }}</label>
                                <input type="time" name="requested_in" class="form-control rounded-3">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-dark">{{ _trans('common.Requested Check-Out') }}</label>
                                <input type="time" name="requested_out" class="form-control rounded-3">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">{{ _trans('common.Reason') }} <span class="text-danger">*</span></label>
                            <textarea name="reason" rows="3" class="form-control rounded-3" placeholder="{{ _trans('common.Explain reason for missing/wrong punch...') }}" required></textarea>
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

    <!-- Approve / Reject Modal (For HR) -->
    <div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST" id="actionForm" action="">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" id="actionInput" value="">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark" id="actionModalTitle">{{ _trans('common.Action Regularization') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="small text-muted mb-3" id="actionModalDesc"></p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">{{ _trans('common.Decision Note (Optional)') }}</label>
                            <textarea name="admin_note" rows="2" class="form-control rounded-3" placeholder="{{ _trans('common.Add any feedback or note for employee...') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn px-4 rounded-3" id="actionSubmitBtn"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openActionModal(formAction, action, employeeName, date) {
        document.getElementById('actionForm').action = formAction;
        document.getElementById('actionInput').value = action;

        const isApprove = action === 'approve';
        document.getElementById('actionModalTitle').textContent = isApprove ? '{{ _trans('common.Approve Regularization') }}' : '{{ _trans('common.Reject Regularization') }}';
        document.getElementById('actionModalDesc').textContent = '{{ _trans('common.You are about to') }} ' + (isApprove ? '{{ _trans('common.approve') }}' : '{{ _trans('common.reject') }}') + ' {{ _trans('common.the attendance regularization for') }} ' + employeeName + ' ({{ _trans('common.Date') }}: ' + date + ').';

        const submitBtn = document.getElementById('actionSubmitBtn');
        if (isApprove) {
            submitBtn.className = 'btn btn-success rounded-3 px-4';
            submitBtn.textContent = '{{ _trans('common.Confirm Approval') }}';
        } else {
            submitBtn.className = 'btn btn-danger rounded-3 px-4';
            submitBtn.textContent = '{{ _trans('common.Confirm Rejection') }}';
        }

        const modal = new bootstrap.Modal(document.getElementById('actionModal'));
        modal.show();
    }
</script>
@endpush
