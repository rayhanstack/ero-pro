@extends('admin.layouts.app')

@section('title', _trans('common.Leave Balances Report'))

@section('content')
    <div class="container-fluid py-3">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ _trans('common.Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leaves.my') }}" class="text-decoration-none">{{ _trans('common.Leaves') }}</a></li>
                        <li class="breadcrumb-item active">{{ _trans('common.Balances Report') }}</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">{{ _trans('common.Employee Leave Balances') }} ({{ $year }})</h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('leaves.calendar') }}" class="btn btn-outline-primary btn-sm rounded-3 d-flex align-items-center gap-1.5">
                    <i class="bi bi-calendar3"></i>
                    <span>{{ _trans('common.Leave Calendar') }}</span>
                </a>
                <a href="{{ route('leaves.requests') }}" class="btn btn-light btn-sm border rounded-3 d-flex align-items-center gap-1.5">
                    <i class="bi bi-inbox"></i>
                    <span>{{ _trans('common.Approval Queue') }}</span>
                </a>
            </div>
        </div>

        <!-- Filters Toolbar -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('leaves.balances') }}" class="row g-2 align-items-center">
                    <!-- Year -->
                    <div class="col-md-2">
                        <select name="year" class="form-select form-select-sm rounded-3">
                            @for ($y = date('Y') + 1; $y >= date('Y') - 3; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Department -->
                    <div class="col-md-3">
                        <select name="department_id" class="form-select form-select-sm rounded-3">
                            <option value="">{{ _trans('common.All Departments') }}</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Employee -->
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="{{ _trans('common.Search employee by name, email or code...') }}" value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm rounded-3 flex-grow-1">
                            <i class="bi bi-filter me-1"></i>{{ _trans('common.Filter') }}
                        </button>
                        <a href="{{ route('leaves.balances') }}" class="btn btn-light btn-sm border rounded-3" title="{{ _trans('common.Reset') }}">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Balances Matrix Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-3" style="min-width: 220px;">{{ _trans('common.Employee') }}</th>
                                @foreach ($leaveTypes as $ltype)
                                    <th class="text-center" style="min-width: 140px;">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <span class="badge rounded-pill px-2 py-0.5 text-white" style="background-color: {{ $ltype->color }}; font-size: 10px;">
                                                {{ $ltype->code }}
                                            </span>
                                            <span>{{ $ltype->name }}</span>
                                        </div>
                                        <div class="text-muted small fw-normal" style="font-size: 10px;">{{ _trans('common.Rem / Tot') }}</div>
                                    </th>
                                @endforeach
                                <th class="text-center" style="min-width: 140px;">{{ _trans('common.Total Leaves') }}</th>
                                @can('leave.manage')
                                    <th class="text-end pe-3" style="min-width: 80px;">{{ _trans('common.Action') }}</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $emp)
                                @php
                                    $empBalances = $emp->leaveBalances->keyBy('leave_type_id');
                                    $grandTotal = 0;
                                    $grandUsed = 0;
                                    $grandRemaining = 0;
                                @endphp
                                <tr>
                                    <!-- Employee Info -->
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $emp->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($emp->name) . '&background=4f46e5&color=fff' }}" class="rounded-circle object-fit-cover flex-shrink-0" width="34" height="34" alt="{{ $emp->name }}">
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $emp->name }}</div>
                                                <div class="text-muted small" style="font-size: 11px;">
                                                    {{ $emp->emp_code ?? $emp->email }} • {{ $emp->department?->name ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Leave Types Balances -->
                                    @foreach ($leaveTypes as $ltype)
                                        @php
                                            $bal = $empBalances->get($ltype->id);
                                            $alloc = $bal ? (float) $bal->allocated : (float) $ltype->days_per_year;
                                            $carried = $bal ? (float) $bal->carried : 0.0;
                                            $used = $bal ? (float) $bal->used : 0.0;
                                            $total = $alloc + $carried;
                                            $rem = max(0, $total - $used);

                                            $grandTotal += $total;
                                            $grandUsed += $used;
                                            $grandRemaining += $rem;
                                        @endphp
                                        <td class="text-center">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <span class="fw-bold text-{{ $rem > 0 ? 'dark' : 'danger' }}">{{ $rem }}</span>
                                                <span class="text-muted small">/ {{ $total }}</span>
                                            </div>
                                            <div class="text-muted small" style="font-size: 10px;">
                                                {{ _trans('common.Used') }}: {{ $used }}
                                            </div>
                                        </td>
                                    @endforeach

                                    <!-- Grand Total -->
                                    <td class="text-center">
                                        <div class="fw-bold text-primary">{{ $grandRemaining }} <span class="text-muted small fw-normal">/ {{ $grandTotal }}</span></div>
                                        <div class="text-muted small" style="font-size: 10px;">{{ _trans('common.Total Used') }}: {{ $grandUsed }}</div>
                                    </td>

                                    <!-- Actions (HR Adjust Balance) -->
                                    @can('leave.manage')
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 py-1 px-2 adjust-balance-btn"
                                                data-employee-id="{{ $emp->id }}"
                                                data-employee-name="{{ $emp->name }}"
                                                data-year="{{ $year }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#adjustBalanceModal"
                                                title="{{ _trans('common.Adjust Balance') }}">
                                                <i class="bi bi-sliders"></i>
                                            </button>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($leaveTypes) + 3 }}" class="text-center py-5 text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        {{ _trans('common.No active employees found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($employees->hasPages())
                <div class="card-footer bg-transparent border-0 py-3">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Adjust Balance Modal -->
    @can('leave.manage')
        <div class="modal fade" id="adjustBalanceModal" tabindex="-1" aria-labelledby="adjustBalanceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST" action="{{ route('leaves.balances.adjust') }}" id="adjustBalanceForm">
                        @csrf
                        <input type="hidden" name="employee_id" id="adjustEmployeeId">
                        <input type="hidden" name="year" id="adjustYear" value="{{ $year }}">

                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold text-dark" id="adjustBalanceModalLabel">
                                <i class="bi bi-sliders text-primary me-2"></i>{{ _trans('common.Adjust Leave Balance') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-3">
                            <div class="p-2.5 bg-light rounded-3 mb-3 border">
                                <div class="small fw-semibold text-dark" id="adjustEmployeeNameDisplay">-</div>
                                <div class="text-muted small" style="font-size: 11px;">{{ _trans('common.Year') }}: {{ $year }}</div>
                            </div>

                            <div class="row g-3">
                                <!-- Leave Type -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Leave Type') }} <span class="text-danger">*</span></label>
                                    <select name="leave_type_id" id="adjustLeaveTypeId" class="form-select" required>
                                        <option value="">-- {{ _trans('common.Select Type') }} --</option>
                                        @foreach ($leaveTypes as $lt)
                                            <option value="{{ $lt->id }}" data-default-days="{{ $lt->days_per_year }}">{{ $lt->name }} ({{ $lt->code }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Allocated Days -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Allocated') }}</label>
                                    <input type="number" step="0.5" name="allocated" id="adjustAllocated" class="form-control" value="0" min="0" max="365" required>
                                </div>

                                <!-- Carried Days -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Carried') }}</label>
                                    <input type="number" step="0.5" name="carried" id="adjustCarried" class="form-control" value="0" min="0" max="365" required>
                                </div>

                                <!-- Used Days -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Used') }}</label>
                                    <input type="number" step="0.5" name="used" id="adjustUsed" class="form-control" value="0" min="0" max="365" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                                <i class="bi bi-check-lg me-1"></i>{{ _trans('common.Save Adjustments') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.adjust-balance-btn').on('click', function() {
            const empId = $(this).data('employee-id');
            const empName = $(this).data('employee-name');
            const year = $(this).data('year');

            $('#adjustEmployeeId').val(empId);
            $('#adjustEmployeeNameDisplay').text(empName);
            $('#adjustYear').val(year);
        });

        $('#adjustLeaveTypeId').on('change', function() {
            const defaultDays = $(this).find(':selected').data('default-days') || 0;
            $('#adjustAllocated').val(defaultDays);
        });
    });
</script>
@endpush
