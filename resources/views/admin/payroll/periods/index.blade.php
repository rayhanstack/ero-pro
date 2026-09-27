@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Payroll Periods'))

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Payroll Periods') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Manage monthly payroll cycles, generate employee payslips, process approvals, and disburse salaries') }}</p>
        </div>
        <div class="d-flex gap-2">
            @can('payroll.create')
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#newPeriodModal">
                    <i class="bi bi-calendar-plus"></i>
                    <span>{{ _trans('common.New Payroll Cycle') }}</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-calendar3 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Periods') }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['total_periods'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-secondary bg-opacity-10 text-secondary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-pencil fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Draft Cycles') }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['draft'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-gear-wide-connected fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Processed Cycles') }}</div>
                        <h4 class="fw-bold mb-0 text-info">{{ $stats['processed'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Paid / Completed') }}</div>
                        <h4 class="fw-bold mb-0 text-success">{{ $stats['paid'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Periods Table --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        @if ($periods->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar2-range text-muted" style="font-size: 3.5rem;"></i>
                <h5 class="fw-bold text-dark mt-3">{{ _trans('common.No Payroll Periods Found') }}</h5>
                <p class="text-muted small mb-3">{{ _trans('common.Create a new monthly payroll cycle to start generating and disbursing employee payslips.') }}</p>
                @can('payroll.create')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPeriodModal">
                        <i class="bi bi-calendar-plus me-1"></i>{{ _trans('common.Start First Payroll Cycle') }}
                    </button>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">{{ _trans('common.Period Cycle') }}</th>
                            <th>{{ _trans('common.Date Range') }}</th>
                            <th>{{ _trans('common.Generated Payslips') }}</th>
                            <th>{{ _trans('common.Total Basic Pay') }}</th>
                            <th>{{ _trans('common.Total Net Payable') }}</th>
                            <th>{{ _trans('common.Status') }}</th>
                            <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($periods as $period)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="bi bi-calendar-check fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $period->formatted_period }}</div>
                                            <div class="text-muted extra-small">{{ _trans('common.Year') }}: {{ $period->year }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="small text-dark">
                                        {{ $period->start_date ? $period->start_date->format('M d, Y') : '-' }} — {{ $period->end_date ? $period->end_date->format('M d, Y') : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2.5 py-1">
                                        <i class="bi bi-file-earmark-person me-1 text-primary"></i>{{ $period->payslips_count }} {{ _trans('common.Payslips') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ currency_format($period->total_basic_pay ?? 0) }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ currency_format($period->total_net_pay ?? 0) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $period->status->badgeClass() }} px-2.5 py-1">
                                        <i class="bi {{ $period->status->icon() }} me-1"></i>{{ $period->status->label() }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @if ($period->payslips_count > 0)
                                            <a href="{{ route('payroll.payslips.index', ['period_id' => $period->id]) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" title="{{ _trans('common.View Payslips') }}">
                                                <i class="bi bi-people"></i>
                                                <span>{{ _trans('common.Payslips') }}</span>
                                            </a>
                                        @endif

                                        @if ($period->status !== \App\Enums\PayrollPeriodStatusEnum::LOCKED)
                                            @can('payroll.create')
                                                <form method="POST" action="{{ route('payroll.periods.generate', $period) }}" class="d-inline" onsubmit="return confirm('{{ $period->payslips_count > 0 ? _trans('common.This will regenerate and overwrite existing draft payslips for this period. Continue?') : _trans('common.Generate payslips for all active employees for this period?') }}')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $period->payslips_count > 0 ? 'btn-outline-warning text-dark' : 'btn-primary' }} d-inline-flex align-items-center gap-1" title="{{ _trans('common.Generate / Recalculate') }}">
                                                        <i class="bi bi-cpu"></i>
                                                        <span>{{ $period->payslips_count > 0 ? _trans('common.Regenerate') : _trans('common.Generate') }}</span>
                                                    </button>
                                                </form>
                                            @endcan

                                            @can('payroll.edit')
                                                <form method="POST" action="{{ route('payroll.periods.lock', $period) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Lock this payroll period? No further modifications will be allowed.') }}')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ _trans('common.Lock Period') }}">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif

                                        @if ($period->status === \App\Enums\PayrollPeriodStatusEnum::DRAFT)
                                            @can('payroll.delete')
                                                <form method="POST" action="{{ route('payroll.periods.destroy', $period) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this draft payroll period?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ _trans('common.Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($periods->hasPages())
                <div class="card-footer bg-white py-3 border-top">
                    {{ $periods->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Create Period Modal --}}
    @can('payroll.create')
        <div class="modal fade" id="newPeriodModal" tabindex="-1" aria-labelledby="newPeriodModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="{{ route('payroll.periods.store') }}" class="needs-validation">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold" id="newPeriodModalLabel">
                                <i class="bi bi-calendar-plus text-primary me-2"></i>{{ _trans('common.Start New Payroll Cycle') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            @if ($errors->any())
                                <div class="alert alert-danger mb-3 py-2 px-3">
                                    <ul class="mb-0 small ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Month') }} <span class="text-danger">*</span></label>
                                    <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ (old('month', date('n')) == $m) ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('month')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Year') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="year" class="form-control @error('year') is-invalid @enderror" value="{{ old('year', date('Y')) }}" min="2020" max="2099" required>
                                    @error('year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 bg-light p-3 rounded-3 border">
                                    <div class="extra-small text-muted">
                                        <i class="bi bi-info-circle text-primary me-1"></i>
                                        {{ _trans('common.The period dates (first and last day of month) will be automatically assigned. You can generate and recalculate employee payslips at any time before locking.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Create Payroll Cycle') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @if ($errors->any())
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var modalEl = document.getElementById('newPeriodModal');
                    if (modalEl) {
                        var modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                });
            </script>
        @endpush
    @endif
@endsection
