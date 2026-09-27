@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Payslip Detail'))

@section('content')
    {{-- Header Action Bar --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 d-print-none">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-bold mb-0">{{ _trans('common.Payslip') }} <span class="font-monospace text-primary">#{{ $payslip->payslip_number }}</span></h3>
                <span class="badge {{ $payslip->status->badgeClass() }} px-2.5 py-1">
                    <i class="bi {{ $payslip->status->icon() }} me-1"></i>{{ $payslip->status->label() }}
                </span>
            </div>
            <p class="text-muted small mb-0">{{ $payslip->payrollPeriod?->formatted_period }} &bull; {{ $payslip->employee?->name }}</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('payroll.payslips.pdf', $payslip) }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-pdf"></i>
                <span>{{ _trans('common.Download PDF') }}</span>
            </a>

            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" onclick="window.print()">
                <i class="bi bi-printer"></i>
                <span>{{ _trans('common.Print') }}</span>
            </button>

            @can('payroll.process')
                @if ($payslip->status === \App\Enums\PayslipStatusEnum::DRAFT)
                    <form method="POST" action="{{ route('payroll.payslips.approve', $payslip) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Approve Payslip') }}</span>
                        </button>
                    </form>
                @elseif ($payslip->status === \App\Enums\PayslipStatusEnum::APPROVED)
                    <form method="POST" action="{{ route('payroll.payslips.mark-paid', $payslip) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Mark payslip as Paid? Finance expense hook will be triggered.') }}')">
                        @csrf
                        <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-1">
                            <i class="bi bi-cash-stack"></i>
                            <span>{{ _trans('common.Mark Paid / Disburse') }}</span>
                        </button>
                    </form>
                @endif
            @endcan

            <a href="{{ url()->previous() ?: route('payroll.payslips.index', ['period_id' => $payslip->payroll_period_id]) }}" class="btn btn-light border">
                <i class="bi bi-arrow-left me-1"></i>{{ _trans('common.Back') }}
            </a>
        </div>
    </div>

    {{-- Printable Payslip Document Card --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mx-auto print-container" style="max-width: 960px;">
        {{-- Company Header --}}
        <div class="row align-items-center pb-4 mb-4 border-bottom">
            <div class="col-sm-7">
                @if (globalSetting('company_logo'))
                    <img src="{{ globalSetting('company_logo') }}" alt="{{ globalSetting('company_name', 'ERP Pro') }}" style="max-height: 48px; max-width: 180px; object-fit: contain;" class="mb-2">
                @else
                    <h3 class="fw-bold text-primary mb-1">{{ globalSetting('company_name', 'ERP Pro Inc.') }}</h3>
                @endif
                <div class="text-muted small">
                    <div>{{ globalSetting('company_address', '100 Enterprise Blvd, Suite 400, Tech City') }}</div>
                    <div>{{ _trans('common.Email') }}: {{ globalSetting('company_email', 'finance@erppro.com') }} &bull; {{ _trans('common.Phone') }}: {{ globalSetting('company_phone', '+1 (555) 019-2834') }}</div>
                </div>
            </div>

            <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                <div class="badge bg-light text-dark border px-3 py-1.5 fs-7 mb-2">{{ _trans('common.OFFICIAL PAYSLIP') }}</div>
                <h5 class="fw-bold text-dark mb-0">{{ $payslip->payrollPeriod?->formatted_period }}</h5>
                <div class="text-muted extra-small mt-1">{{ _trans('common.Payslip No') }}: <span class="font-monospace fw-bold text-dark">{{ $payslip->payslip_number }}</span></div>
                <div class="text-muted extra-small">{{ _trans('common.Generated') }}: {{ $payslip->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        {{-- Employee & Payment Details Box --}}
        <div class="p-3 bg-light rounded-4 border mb-4">
            <div class="row g-3 small">
                <div class="col-sm-6 col-md-3">
                    <span class="text-muted extra-small d-block">{{ _trans('common.Employee Name') }}</span>
                    <strong class="text-dark">{{ $payslip->employee?->name }}</strong>
                </div>

                <div class="col-sm-6 col-md-3">
                    <span class="text-muted extra-small d-block">{{ _trans('common.Employee Code') }}</span>
                    <strong class="text-dark">{{ $payslip->employee?->employeeDetail?->emp_code ?? 'EMP' }}</strong>
                </div>

                <div class="col-sm-6 col-md-3">
                    <span class="text-muted extra-small d-block">{{ _trans('common.Designation & Dept') }}</span>
                    <span class="text-dark">{{ $payslip->employee?->employeeDetail?->designation?->name ?? 'Staff' }} ({{ $payslip->employee?->employeeDetail?->department?->name ?? 'General' }})</span>
                </div>

                <div class="col-sm-6 col-md-3">
                    <span class="text-muted extra-small d-block">{{ _trans('common.Joining Date') }}</span>
                    <span class="text-dark">{{ $payslip->employee?->employeeDetail?->joining_date ? $payslip->employee->employeeDetail->joining_date->format('M d, Y') : '-' }}</span>
                </div>

                <div class="col-sm-6 col-md-3">
                    <span class="text-muted extra-small d-block">{{ _trans('common.Bank Account') }}</span>
                    <span class="text-dark">{{ $payslip->employee?->primaryBankAccount?->bank ?? _trans('common.Bank') }} ({{ $payslip->employee?->primaryBankAccount?->account_no ?? '••••' }})</span>
                </div>

                <div class="col-sm-6 col-md-3">
                    <span class="text-muted extra-small d-block">{{ _trans('common.Payment Method') }}</span>
                    <span class="text-dark">{{ $payslip->payment_method ?? _trans('common.Bank Transfer') }}</span>
                </div>

                <div class="col-sm-6 col-md-3">
                    <span class="text-muted extra-small d-block">{{ _trans('common.Payment Date') }}</span>
                    <span class="text-dark">{{ $payslip->paid_at ? $payslip->paid_at->format('M d, Y') : _trans('common.Pending') }}</span>
                </div>

                <div class="col-sm-6 col-md-3">
                    <span class="text-muted extra-small d-block">{{ _trans('common.Disbursement Status') }}</span>
                    <span class="badge {{ $payslip->status->badgeClass() }}">{{ $payslip->status->label() }}</span>
                </div>
            </div>
        </div>

        {{-- Attendance Summary Mini Bar --}}
        <div class="row g-2 text-center mb-4 small">
            <div class="col-3">
                <div class="p-2 border rounded-3 bg-white shadow-2xs">
                    <div class="text-muted extra-small">{{ _trans('common.Working Days') }}</div>
                    <div class="fw-bold text-dark fs-6">{{ number_format($payslip->working_days, 1) }}</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 border rounded-3 bg-white shadow-2xs">
                    <div class="text-muted extra-small">{{ _trans('common.Present Days') }}</div>
                    <div class="fw-bold text-success fs-6">{{ number_format($payslip->present_days, 1) }}</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 border rounded-3 bg-white shadow-2xs">
                    <div class="text-muted extra-small">{{ _trans('common.Leave Days') }}</div>
                    <div class="fw-bold text-info fs-6">{{ number_format($payslip->leave_days, 1) }}</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 border rounded-3 bg-white shadow-2xs">
                    <div class="text-muted extra-small">{{ _trans('common.Absent Days') }}</div>
                    <div class="fw-bold text-danger fs-6">{{ number_format($payslip->absent_days, 1) }}</div>
                </div>
            </div>
        </div>

        {{-- Earnings vs Deductions Table --}}
        <div class="row g-4 mb-4">
            {{-- Earnings Column --}}
            <div class="col-md-6">
                <div class="card border rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-success bg-opacity-10 py-2.5 border-bottom">
                        <h6 class="fw-bold text-success mb-0">
                            <i class="bi bi-plus-circle me-1"></i>{{ _trans('common.Earnings & Allowances') }}
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless mb-0 align-middle">
                            <tbody>
                                <tr class="border-bottom">
                                    <td class="ps-3 py-2 fw-semibold text-dark">{{ _trans('common.Basic Salary') }}</td>
                                    <td class="text-end pe-3 py-2 fw-bold text-dark">{{ currency_format($payslip->basic) }}</td>
                                </tr>
                                @foreach ($payslip->earnings as $item)
                                    <tr class="border-bottom">
                                        <td class="ps-3 py-2 text-dark">
                                            {{ $item->name }}
                                            @if ($item->calc_type === 'percent_of_basic')
                                                <span class="text-muted extra-small">({{ $item->rate_or_value }}%)</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3 py-2 text-dark">{{ currency_format($item->amount) }}</td>
                                    </tr>
                                @endforeach
                                @if ($payslip->overtime_amount > 0)
                                    <tr class="border-bottom">
                                        <td class="ps-3 py-2 text-dark">{{ _trans('common.Overtime Allowance') }}</td>
                                        <td class="text-end pe-3 py-2 text-success">+{{ currency_format($payslip->overtime_amount) }}</td>
                                    </tr>
                                @endif
                                @if ($payslip->bonus > 0)
                                    <tr class="border-bottom">
                                        <td class="ps-3 py-2 text-dark">{{ _trans('common.Bonus / Incentive') }}</td>
                                        <td class="text-end pe-3 py-2 text-success">+{{ currency_format($payslip->bonus) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-light border-top">
                                <tr>
                                    <th class="ps-3 py-2 fw-bold text-dark">{{ _trans('common.Total Gross Earnings') }}</th>
                                    <th class="text-end pe-3 py-2 fw-bold text-success">{{ currency_format($payslip->gross_salary) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Deductions Column --}}
            <div class="col-md-6">
                <div class="card border rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-danger bg-opacity-10 py-2.5 border-bottom">
                        <h6 class="fw-bold text-danger mb-0">
                            <i class="bi bi-dash-circle me-1"></i>{{ _trans('common.Deductions & Taxes') }}
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless mb-0 align-middle">
                            <tbody>
                                @foreach ($payslip->deductions as $item)
                                    <tr class="border-bottom">
                                        <td class="ps-3 py-2 text-dark">
                                            {{ $item->name }}
                                            @if ($item->calc_type === 'percent_of_basic')
                                                <span class="text-muted extra-small">({{ $item->rate_or_value }}%)</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3 py-2 text-danger">-{{ currency_format($item->amount) }}</td>
                                    </tr>
                                @endforeach
                                @if ($payslip->absent_deduction > 0)
                                    <tr class="border-bottom">
                                        <td class="ps-3 py-2 text-dark">{{ _trans('common.Absenteeism Deduction') }}</td>
                                        <td class="text-end pe-3 py-2 text-danger">-{{ currency_format($payslip->absent_deduction) }}</td>
                                    </tr>
                                @endif
                                @if ($payslip->tax > 0)
                                    <tr class="border-bottom">
                                        <td class="ps-3 py-2 text-dark">{{ _trans('common.Income Tax Deduction') }}</td>
                                        <td class="text-end pe-3 py-2 text-danger">-{{ currency_format($payslip->tax) }}</td>
                                    </tr>
                                @endif
                                @if ($payslip->deductions->isEmpty() && $payslip->absent_deduction <= 0 && $payslip->tax <= 0)
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted small fst-italic">{{ _trans('common.No deductions recorded') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-light border-top">
                                <tr>
                                    <th class="ps-3 py-2 fw-bold text-dark">{{ _trans('common.Total Deductions') }}</th>
                                    <th class="text-end pe-3 py-2 fw-bold text-danger">-{{ currency_format($payslip->total_deductions + $payslip->absent_deduction + $payslip->tax) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Net Pay Highlight Banner --}}
        <div class="p-4 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-4 d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <span class="text-success extra-small fw-semibold text-uppercase tracking-wider d-block">{{ _trans('common.Net Salary Payable') }}</span>
                <div class="fs-2 fw-bold text-success mt-0.5">{{ currency_format($payslip->net_pay) }}</div>
            </div>
            <div class="text-muted small">
                {{ _trans('common.Gross Pay') }}: <strong>{{ currency_format($payslip->gross_salary) }}</strong> &bull;
                {{ _trans('common.Total Deductions') }}: <strong class="text-danger">-{{ currency_format($payslip->total_deductions + $payslip->absent_deduction + $payslip->tax) }}</strong>
            </div>
        </div>

        @if ($payslip->note)
            <div class="p-3 bg-light rounded-3 border mb-4">
                <strong class="extra-small text-muted d-block mb-1">{{ _trans('common.Remarks & Notes') }}:</strong>
                <p class="small text-dark mb-0">{{ $payslip->note }}</p>
            </div>
        @endif

        {{-- Signatures Footer --}}
        <div class="row pt-5 mt-4 text-center">
            <div class="col-6">
                <div class="d-inline-block border-top pt-2" style="width: 200px;">
                    <span class="extra-small text-muted fw-semibold">{{ _trans('common.Employee Signature') }}</span>
                </div>
            </div>
            <div class="col-6">
                <div class="d-inline-block border-top pt-2" style="width: 200px;">
                    <span class="extra-small text-muted fw-semibold">{{ _trans('common.Authorized Signatory') }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
@media print {
    body {
        background-color: #ffffff !important;
    }
    .sidebar, .navbar, .d-print-none, .app-header {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .print-container {
        box-shadow: none !important;
        border: none !important;
        max-width: 100% !important;
        padding: 0 !important;
    }
}
</style>
@endpush
