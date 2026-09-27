@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Payslips'))

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-bold mb-0">{{ _trans('common.Payslips') }}</h3>
                <span class="badge {{ $period->status->badgeClass() }} px-2.5 py-1">
                    <i class="bi {{ $period->status->icon() }} me-1"></i>{{ $period->status->label() }}
                </span>
            </div>
            <p class="text-muted small mb-0">{{ _trans('common.Review, adjust bonuses/deductions, approve, and disburse payslips for :period', ['period' => $period->formatted_period]) }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- Period Switcher --}}
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-calendar3"></i>
                    <span>{{ $period->formatted_period }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    @foreach ($allPeriods as $p)
                        <li>
                            <a class="dropdown-item d-flex justify-content-between align-items-center {{ $p->id === $period->id ? 'active' : '' }}" href="{{ route('payroll.payslips.index', ['period_id' => $p->id]) }}">
                                <span>{{ $p->formatted_period }}</span>
                                <span class="badge {{ $p->status->badgeClass() }} ms-2 extra-small">{{ $p->status->label() }}</span>
                            </a>
                        </li>
                    @endforeach
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-primary" href="{{ route('payroll.periods.index') }}">
                            <i class="bi bi-gear me-1"></i>{{ _trans('common.Manage All Periods') }}
                        </a>
                    </li>
                </ul>
            </div>

            @can('payroll.create')
                @if ($period->status !== \App\Enums\PayrollPeriodStatusEnum::LOCKED)
                    <form method="POST" action="{{ route('payroll.periods.generate', $period) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Regenerate payslips for all active employees for this period? Manual adjustments will be reset.') }}')">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning text-dark d-inline-flex align-items-center gap-1">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>{{ _trans('common.Regenerate') }}</span>
                        </button>
                    </form>
                @endif
            @endcan

            @can('payroll.process')
                @if ($stats['total'] > 0 && $period->status !== \App\Enums\PayrollPeriodStatusEnum::LOCKED)
                    <form method="POST" action="{{ route('payroll.periods.bulk-approve', $period) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-all"></i>
                            <span>{{ _trans('common.Approve All') }}</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('payroll.periods.bulk-mark-paid', $period) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Disburse and mark all approved payslips as PAID? Finance expense events will be triggered.') }}')">
                        @csrf
                        <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-1">
                            <i class="bi bi-cash-stack"></i>
                            <span>{{ _trans('common.Disburse All') }}</span>
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-file-earmark-text fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Payslips') }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Approved Payslips') }}</div>
                        <h4 class="fw-bold mb-0 text-info">{{ $stats['approved'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Paid / Disbursed') }}</div>
                        <h4 class="fw-bold mb-0 text-success">{{ $stats['paid'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Net Payable') }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ currency_format($stats['total_net']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('payroll.payslips.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="period_id" value="{{ $period->id }}">

                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="{{ _trans('common.Search by employee name, code, payslip #...') }}" value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="department_id" class="form-select">
                        <option value="">{{ _trans('common.All Departments') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">{{ _trans('common.All Statuses') }}</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ ($filters['status'] ?? '') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>{{ _trans('common.Filter') }}
                    </button>
                    <a href="{{ route('payroll.payslips.index', ['period_id' => $period->id]) }}" class="btn btn-light border" title="{{ _trans('common.Reset') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Payslips Table --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        @if ($payslips->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-receipt text-muted" style="font-size: 3.5rem;"></i>
                <h5 class="fw-bold text-dark mt-3">{{ _trans('common.No Payslips Found') }}</h5>
                <p class="text-muted small mb-3">{{ _trans('common.Click "Generate" above to calculate employee payslips for this cycle.') }}</p>
                @can('payroll.create')
                    <form method="POST" action="{{ route('payroll.periods.generate', $period) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cpu me-1"></i>{{ _trans('common.Generate Payslips Now') }}
                        </button>
                    </form>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">{{ _trans('common.Payslip # & Employee') }}</th>
                            <th>{{ _trans('common.Department') }}</th>
                            <th>{{ _trans('common.Basic Salary') }}</th>
                            <th>{{ _trans('common.Allowances (+)') }}</th>
                            <th>{{ _trans('common.Deductions (-)') }}</th>
                            <th>{{ _trans('common.Overtime (+)') }}</th>
                            <th>{{ _trans('common.Bonus (+)') }}</th>
                            <th>{{ _trans('common.Net Pay') }}</th>
                            <th>{{ _trans('common.Status') }}</th>
                            <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payslips as $payslip)
                            @php $emp = $payslip->employee; @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <img src="{{ $emp?->avatar_url }}" alt="{{ $emp?->name }}" class="rounded-circle object-fit-cover shadow-sm" width="36" height="36">
                                        <div>
                                            <div class="fw-bold text-dark">
                                                <a href="{{ route('payroll.payslips.show', $payslip) }}" class="text-dark text-decoration-none hover-primary">
                                                    {{ $emp?->name ?? _trans('common.Unknown') }}
                                                </a>
                                            </div>
                                            <div class="text-muted extra-small">
                                                <span class="font-monospace text-primary me-1">{{ $payslip->payslip_number }}</span>
                                                <span>{{ $emp?->employeeDetail?->emp_code }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-dark">{{ $emp?->employeeDetail?->designation?->name ?? _trans('common.Staff') }}</div>
                                    <div class="text-muted extra-small">{{ $emp?->employeeDetail?->department?->name ?? _trans('common.General') }}</div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ currency_format($payslip->basic) }}</span>
                                </td>
                                <td>
                                    <span class="small text-success">+{{ currency_format($payslip->total_earnings) }}</span>
                                </td>
                                <td>
                                    <span class="small text-danger">-{{ currency_format($payslip->total_deductions + $payslip->absent_deduction + $payslip->tax) }}</span>
                                </td>
                                <td>
                                    @if ($payslip->overtime_amount > 0)
                                        <span class="small text-success">+{{ currency_format($payslip->overtime_amount) }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($payslip->bonus > 0)
                                        <span class="small text-success fw-semibold">+{{ currency_format($payslip->bonus) }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 fs-8">
                                        {{ currency_format($payslip->net_pay) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $payslip->status->badgeClass() }} px-2 py-1">
                                        <i class="bi {{ $payslip->status->icon() }} me-1"></i>{{ $payslip->status->label() }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="{{ route('payroll.payslips.show', $payslip) }}" class="btn btn-sm btn-outline-secondary p-1 px-2" title="{{ _trans('common.View Payslip') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="{{ route('payroll.payslips.pdf', $payslip) }}" class="btn btn-sm btn-outline-primary p-1 px-2" title="{{ _trans('common.Download PDF') }}">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>

                                        @if ($payslip->status !== \App\Enums\PayslipStatusEnum::PAID && $period->status !== \App\Enums\PayrollPeriodStatusEnum::LOCKED)
                                            @can('payroll.edit')
                                                <button type="button" class="btn btn-sm btn-outline-info p-1 px-2 edit-payslip-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPayslipModal"
                                                    data-id="{{ $payslip->id }}"
                                                    data-name="{{ $emp?->name }}"
                                                    data-number="{{ $payslip->payslip_number }}"
                                                    data-bonus="{{ $payslip->bonus }}"
                                                    data-tax="{{ $payslip->tax }}"
                                                    data-overtime="{{ $payslip->overtime_amount }}"
                                                    data-absent="{{ $payslip->absent_deduction }}"
                                                    data-note="{{ $payslip->note }}"
                                                    data-action="{{ route('payroll.payslips.update', $payslip) }}"
                                                    title="{{ _trans('common.Adjust Bonus / Tax') }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            @endcan

                                            @can('payroll.process')
                                                @if ($payslip->status === \App\Enums\PayslipStatusEnum::DRAFT)
                                                    <form method="POST" action="{{ route('payroll.payslips.approve', $payslip) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success p-1 px-2" title="{{ _trans('common.Approve') }}">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($payslip->status === \App\Enums\PayslipStatusEnum::APPROVED)
                                                    <form method="POST" action="{{ route('payroll.payslips.mark-paid', $payslip) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Mark payslip as PAID for :name? Finance expense hook will be triggered.', ['name' => $emp?->name]) }}')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success p-1 px-2" title="{{ _trans('common.Mark Paid') }}">
                                                            <i class="bi bi-cash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($payslips->hasPages())
                <div class="card-footer bg-white py-3 border-top">
                    {{ $payslips->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Quick Edit Adjustments Modal --}}
    <div class="modal fade" id="editPayslipModal" tabindex="-1" aria-labelledby="editPayslipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <form method="POST" action="" id="editPayslipForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-light">
                        <div>
                            <h5 class="modal-title fw-bold" id="editPayslipModalLabel">{{ _trans('common.Adjust Payslip') }}</h5>
                            <span class="text-muted extra-small" id="modalEmployeeLabel"></span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">{{ _trans('common.Bonus / Incentive (+)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" step="0.01" min="0" name="bonus" id="modal_bonus" class="form-control" placeholder="0.00">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">{{ _trans('common.Income Tax Deduction (-)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" step="0.01" min="0" name="tax" id="modal_tax" class="form-control" placeholder="0.00">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">{{ _trans('common.Overtime Pay (+)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" step="0.01" min="0" name="overtime_amount" id="modal_overtime" class="form-control" placeholder="0.00">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">{{ _trans('common.Absent Deduction (-)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" step="0.01" min="0" name="absent_deduction" id="modal_absent" class="form-control" placeholder="0.00">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">{{ _trans('common.Payslip Remarks / Note') }}</label>
                                <textarea name="note" id="modal_note" class="form-control" rows="3" placeholder="{{ _trans('common.Optional note printed on payslip...') }}"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check2"></i>
                            <span>{{ _trans('common.Save Adjustments') }}</span>
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
    $('.edit-payslip-btn').on('click', function() {
        var $btn = $(this);
        var actionUrl = $btn.data('action');
        var name = $btn.data('name');
        var number = $btn.data('number');
        var bonus = $btn.data('bonus');
        var tax = $btn.data('tax');
        var overtime = $btn.data('overtime');
        var absent = $btn.data('absent');
        var note = $btn.data('note');

        $('#editPayslipForm').attr('action', actionUrl);
        $('#modalEmployeeLabel').text(name + ' (' + number + ')');
        $('#modal_bonus').val(bonus);
        $('#modal_tax').val(tax);
        $('#modal_overtime').val(overtime);
        $('#modal_absent').val(absent);
        $('#modal_note').val(note);
    });
});
</script>
@endpush
