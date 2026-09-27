@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.My Payslips'))

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.My Payslips') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.View and download your monthly salary statements and breakdown') }}</p>
        </div>
        @if ($employee)
            <div class="d-flex align-items-center gap-3 bg-white p-2.5 px-3 rounded-4 shadow-sm border">
                <img src="{{ $employee->profile_photo_url }}" class="rounded-circle" width="36" height="36" alt="{{ $employee->full_name }}">
                <div>
                    <div class="fw-semibold text-dark small">{{ $employee->full_name }}</div>
                    <div class="text-muted extra-small">{{ $employee->designation?->name ?? _trans('common.Employee') }} • {{ $employee->employee_id }}</div>
                </div>
            </div>
        @endif
    </div>

    @if (! $employee)
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 text-center">
            <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2 text-warning"></i>
            <h5 class="fw-bold mb-1">{{ _trans('common.No Employee Profile Associated') }}</h5>
            <p class="text-muted small mb-0">{{ _trans('common.Your user account is not linked to an employee profile.') }}</p>
        </div>
    @else
        {{-- Quick Stats --}}
        @php
            $totalReceived = $payslips->where('status', \App\Enums\PayslipStatusEnum::PAID)->sum('net_pay');
            $latestPayslip = $payslips->first();
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-file-earmark-check fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Statements') }}</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $payslips->total() }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Net Received') }}</div>
                            <h4 class="fw-bold mb-0 text-success">{{ format_currency($totalReceived) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-calendar-check fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted extra-small fw-medium">{{ _trans('common.Latest Period') }}</div>
                            <h5 class="fw-bold mb-0 text-dark">{{ $latestPayslip ? $latestPayslip->payrollPeriod->formatted_period : 'N/A' }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payslips Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h5 class="fw-bold mb-0">{{ _trans('common.Salary History') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light text-muted extra-small text-uppercase">
                        <tr>
                            <th class="ps-4">{{ _trans('common.Period') }}</th>
                            <th>{{ _trans('common.Basic Salary') }}</th>
                            <th>{{ _trans('common.Earnings') }}</th>
                            <th>{{ _trans('common.Deductions') }}</th>
                            <th>{{ _trans('common.Net Pay') }}</th>
                            <th>{{ _trans('common.Status') }}</th>
                            <th>{{ _trans('common.Disbursed Date') }}</th>
                            <th class="text-end pe-4">{{ _trans('common.Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse ($payslips as $slip)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $slip->payrollPeriod->formatted_period }}</div>
                                    <div class="text-muted extra-small">
                                        {{ $slip->present_days }}/{{ $slip->working_days }} {{ _trans('common.Days Present') }}
                                        @if ($slip->absent_days > 0)
                                            • <span class="text-danger">{{ $slip->absent_days }} {{ _trans('common.absent') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="fw-semibold text-dark">{{ format_currency($slip->basic) }}</td>
                                <td class="text-success fw-medium">
                                    +{{ format_currency($slip->total_earnings + $slip->overtime_amount + $slip->bonus) }}
                                </td>
                                <td class="text-danger fw-medium">
                                    -{{ format_currency($slip->total_deductions + $slip->absent_deduction + $slip->tax) }}
                                </td>
                                <td>
                                    <span class="fs-6 fw-bold text-dark">{{ format_currency($slip->net_pay) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $slip->status->badgeClass() }} px-2 py-1">
                                        <i class="bi {{ $slip->status->icon() }} me-1"></i>{{ $slip->status->label() }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $slip->paid_at ? format_date($slip->paid_at) : '—' }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="{{ route('payroll.payslips.show', $slip) }}" class="btn btn-sm btn-light text-primary" title="{{ _trans('common.View Statement') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('payroll.payslips.pdf', $slip) }}" class="btn btn-sm btn-light text-danger" title="{{ _trans('common.Download PDF') }}">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder2-open display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    {{ _trans('common.No payslips found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payslips->hasPages())
                <div class="card-footer bg-white border-0 py-3 px-4">
                    {{ $payslips->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
