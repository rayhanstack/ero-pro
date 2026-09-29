@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Invoices'))

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Invoices & Billing') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Manage client invoices, billing schedules, receipts, and payment settlements') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @can('finance.create')
                <a href="{{ route('finance.invoices.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Create Invoice') }}</span>
                </a>
            @endcan
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-receipt-cutoff fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Invoiced') }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ currency_format($summary['total_invoiced']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Received') }}</div>
                        <h4 class="fw-bold mb-0 text-success">{{ currency_format($summary['total_paid']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Due / Pending') }}</div>
                        <h4 class="fw-bold mb-0 text-warning">{{ currency_format($summary['total_due']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Overdue') }}</div>
                        <h4 class="fw-bold mb-0 text-danger">{{ currency_format($summary['total_overdue']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('finance.invoices.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">{{ _trans('common.Search / Invoice #') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="{{ _trans('common.Invoice #, Client name...') }}" value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">{{ _trans('common.Status') }}</label>
                        <select name="status" class="form-select bg-light">
                            <option value="">{{ _trans('common.All Statuses') }}</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">{{ _trans('common.Client') }}</label>
                        <select name="client_id" class="form-select bg-light select2">
                            <option value="">{{ _trans('common.All Clients') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ ($filters['client_id'] ?? '') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">{{ _trans('common.Project') }}</label>
                        <select name="project_id" class="form-select bg-light select2">
                            <option value="">{{ _trans('common.All Projects') }}</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ ($filters['project_id'] ?? '') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-1.5">
                            <i class="bi bi-funnel"></i>
                            <span>{{ _trans('common.Filter') }}</span>
                        </button>
                        <a href="{{ route('finance.invoices.index') }}" class="btn btn-light border w-auto px-3" title="{{ _trans('common.Reset Filters') }}">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Invoices Table Card --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted extra-small text-uppercase">
                        <tr>
                            <th class="ps-4">{{ _trans('common.Invoice #') }}</th>
                            <th>{{ _trans('common.Client') }}</th>
                            <th>{{ _trans('common.Project') }}</th>
                            <th>{{ _trans('common.Issue Date') }}</th>
                            <th>{{ _trans('common.Due Date') }}</th>
                            <th class="text-end">{{ _trans('common.Total') }}</th>
                            <th class="text-end">{{ _trans('common.Paid') }}</th>
                            <th class="text-end">{{ _trans('common.Due') }}</th>
                            <th class="text-center">{{ _trans('common.Status') }}</th>
                            <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('finance.invoices.show', $invoice->id) }}" class="fw-bold text-primary text-decoration-none">
                                        #{{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    @if($invoice->client)
                                        <div class="fw-semibold text-dark">{{ $invoice->client->name }}</div>
                                        <small class="text-muted">{{ $invoice->client->email }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($invoice->project)
                                        <span class="badge bg-light text-dark border">{{ $invoice->project->name }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="small">{{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : '—' }}</span>
                                </td>
                                <td>
                                    <span class="small {{ $invoice->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                        {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    {{ currency_format($invoice->total_amount) }}
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    {{ currency_format($invoice->paid_amount) }}
                                </td>
                                <td class="text-end fw-bold {{ $invoice->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ currency_format($invoice->due_amount) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $invoice->status->badgeClass() }} px-2.5 py-1.5 rounded-pill">
                                        {{ $invoice->status->label() }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                            <li>
                                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('finance.invoices.show', $invoice->id) }}">
                                                    <i class="bi bi-eye text-primary"></i>
                                                    <span>{{ _trans('common.View Invoice') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('finance.invoices.download-pdf', $invoice->id) }}" target="_blank">
                                                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                                                    <span>{{ _trans('common.Download PDF') }}</span>
                                                </a>
                                            </li>
                                            @can('finance.edit')
                                                @if($invoice->status->value !== 'paid')
                                                    <li>
                                                        <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('finance.invoices.edit', $invoice->id) }}">
                                                            <i class="bi bi-pencil text-warning"></i>
                                                            <span>{{ _trans('common.Edit Invoice') }}</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endcan
                                            @can('finance.delete')
                                                <li>
                                                    <form action="{{ route('finance.invoices.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this invoice?') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger">
                                                            <i class="bi bi-trash"></i>
                                                            <span>{{ _trans('common.Delete') }}</span>
                                                        </button>
                                                    </form>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-receipt display-6 d-block text-muted opacity-50 mb-2"></i>
                                    {{ _trans('common.No invoices found matching your criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="card-footer bg-transparent border-0 px-4 py-3">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
