@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Income Transactions'))

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Income Transactions') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Track all incoming revenues, sales deposits, and client settlements') }}</p>
        </div>

        @can('finance.create')
            <button type="button" class="btn btn-success d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newIncomeModal">
                <i class="bi bi-plus-lg"></i>
                <span>{{ _trans('common.Add Income') }}</span>
            </button>
        @endcan
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-funnel-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Filtered Total') }}</div>
                        <h4 class="fw-bold mb-0 text-success">{{ currency_format($stats['filtered_total']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-calendar3 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.This Month') }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ currency_format($stats['this_month']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Today') }}</div>
                        <h4 class="fw-bold mb-0 text-info">{{ currency_format($stats['today']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-dark bg-opacity-10 text-dark p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.All Time Total') }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ currency_format($stats['all_time']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Incomes Table Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        {{-- Filters Bar --}}
        <div class="card-header bg-white border-0 py-3 px-4">
            <form method="GET" action="{{ route('finance.income.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3 col-lg-2">
                    <input type="text" name="search" class="form-control bg-light" placeholder="{{ _trans('common.Search reference...') }}" value="{{ $filters['search'] ?? '' }}">
                </div>

                <div class="col-md-3 col-lg-2">
                    <select name="account_id" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">{{ _trans('common.All Accounts') }}</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ ($filters['account_id'] ?? '') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-lg-2">
                    <select name="category_id" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">{{ _trans('common.All Categories') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-lg-2">
                    <select name="client_id" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">{{ _trans('common.All Clients') }}</option>
                        @foreach ($clients as $cl)
                            <option value="{{ $cl->id }}" {{ ($filters['client_id'] ?? '') == $cl->id ? 'selected' : '' }}>
                                {{ $cl->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-lg-2">
                    <input type="date" name="start_date" class="form-control bg-light" placeholder="From Date" value="{{ $filters['start_date'] ?? '' }}">
                </div>

                <div class="col-md-3 col-lg-2">
                    <input type="date" name="end_date" class="form-control bg-light" placeholder="To Date" value="{{ $filters['end_date'] ?? '' }}">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">{{ _trans('common.Filter') }}</button>
                    @if (! empty(array_filter($filters)))
                        <a href="{{ route('finance.income.index') }}" class="btn btn-light" title="{{ _trans('common.Reset') }}">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light text-muted extra-small text-uppercase">
                    <tr>
                        <th class="ps-4">{{ _trans('common.Transaction & Date') }}</th>
                        <th>{{ _trans('common.Account') }}</th>
                        <th>{{ _trans('common.Category') }}</th>
                        <th>{{ _trans('common.Client / Project') }}</th>
                        <th>{{ _trans('common.Payment Method') }}</th>
                        <th>{{ _trans('common.Amount') }}</th>
                        <th class="text-end pe-4">{{ _trans('common.Action') }}</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse ($incomes as $trx)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $trx->transaction_number }}</div>
                                <div class="text-muted extra-small">{{ formatDate($trx->date) }}</div>
                                @if ($trx->reference)
                                    <span class="badge bg-light text-muted border extra-small">{{ $trx->reference }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $trx->account?->name ?? '—' }}</div>
                                <div class="text-muted extra-small">{{ $trx->account?->account_number }}</div>
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="bi bi-tag-fill me-1"></i>{{ $trx->category_name }}
                                </span>
                            </td>
                            <td>
                                @if ($trx->client)
                                    <div class="fw-semibold text-dark">{{ $trx->client->company_name }}</div>
                                @endif
                                @if ($trx->project)
                                    <div class="text-muted extra-small"><i class="bi bi-folder me-1"></i>{{ $trx->project->title }}</div>
                                @endif
                                @if (! $trx->client && ! $trx->project)
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-dark small">{{ $trx->payment_method ?: 'Cash' }}</span>
                            </td>
                            <td>
                                <span class="fs-6 fw-bold text-success">+{{ currency_format($trx->amount) }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @if ($trx->attachment)
                                        <a href="{{ $trx->attachment_url }}" target="_blank" class="btn btn-sm btn-light text-info" title="{{ _trans('common.View Attachment') }}">
                                            <i class="bi bi-paperclip"></i>
                                        </a>
                                    @endif

                                    @can('finance.edit')
                                        <button type="button" class="btn btn-sm btn-light text-primary edit-income-btn"
                                            data-id="{{ $trx->id }}"
                                            data-account-id="{{ $trx->account_id }}"
                                            data-category-id="{{ $trx->category_id }}"
                                            data-amount="{{ $trx->amount }}"
                                            data-date="{{ $trx->date->format('Y-m-d') }}"
                                            data-reference="{{ $trx->reference }}"
                                            data-payment-method="{{ $trx->payment_method }}"
                                            data-client-id="{{ $trx->client_id }}"
                                            data-project-id="{{ $trx->project_id }}"
                                            data-note="{{ $trx->note }}"
                                            title="{{ _trans('common.Edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endcan

                                    @can('finance.delete')
                                        <form method="POST" action="{{ route('finance.income.destroy', $trx) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this income transaction?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" title="{{ _trans('common.Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-wallet display-6 d-block mb-2 text-secondary opacity-50"></i>
                                {{ _trans('common.No income transactions found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($incomes->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $incomes->links() }}
            </div>
        @endif
    </div>

    {{-- Create Income Modal --}}
    @can('finance.create')
        <div class="modal fade" id="newIncomeModal" tabindex="-1" aria-labelledby="newIncomeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="{{ route('finance.income.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="income">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold text-success" id="newIncomeModalLabel">
                                <i class="bi bi-arrow-down-left-circle-fill me-2"></i>{{ _trans('common.Record Income Transaction') }}
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
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Deposit Account') }} <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-select" required>
                                        <option value="">{{ _trans('common.Select Account') }}</option>
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ currency_format($acc->balance) }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Income Category') }}</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">{{ _trans('common.Select Category') }}</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Amount') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Transaction Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Payment Method') }}</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Credit Card">Credit Card</option>
                                        <option value="Mobile Payment">Mobile Payment</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Reference / Receipt No') }}</label>
                                    <input type="text" name="reference" class="form-control" placeholder="e.g. REC-8921">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Client') }}</label>
                                    <select name="client_id" class="form-select">
                                        <option value="">{{ _trans('common.None / General') }}</option>
                                        @foreach ($clients as $cl)
                                            <option value="{{ $cl->id }}">{{ $cl->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Project') }}</label>
                                    <select name="project_id" class="form-select">
                                        <option value="">{{ _trans('common.None / General') }}</option>
                                        @foreach ($projects as $proj)
                                            <option value="{{ $proj->id }}">{{ $proj->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Attachment / Receipt') }}</label>
                                    <input type="file" name="attachment" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Note / Description') }}</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="{{ _trans('common.Additional details about this income...') }}"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Save Income') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit Income Modal --}}
        <div class="modal fade" id="editIncomeModal" tabindex="-1" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" id="editIncomeForm" action="" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="type" value="income">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold text-primary" id="editIncomeModalLabel">
                                <i class="bi bi-pencil-square me-2"></i>{{ _trans('common.Edit Income Transaction') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Deposit Account') }} <span class="text-danger">*</span></label>
                                    <select name="account_id" id="edit_account_id" class="form-select" required>
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ currency_format($acc->balance) }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Income Category') }}</label>
                                    <select name="category_id" id="edit_category_id" class="form-select">
                                        <option value="">{{ _trans('common.Select Category') }}</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Amount') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="edit_amount" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Transaction Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="edit_date" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Payment Method') }}</label>
                                    <select name="payment_method" id="edit_payment_method" class="form-select">
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Credit Card">Credit Card</option>
                                        <option value="Mobile Payment">Mobile Payment</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Reference / Receipt No') }}</label>
                                    <input type="text" name="reference" id="edit_reference" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Client') }}</label>
                                    <select name="client_id" id="edit_client_id" class="form-select">
                                        <option value="">{{ _trans('common.None / General') }}</option>
                                        @foreach ($clients as $cl)
                                            <option value="{{ $cl->id }}">{{ $cl->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Project') }}</label>
                                    <select name="project_id" id="edit_project_id" class="form-select">
                                        <option value="">{{ _trans('common.None / General') }}</option>
                                        @foreach ($projects as $proj)
                                            <option value="{{ $proj->id }}">{{ $proj->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Attachment / Receipt (Optional replacement)') }}</label>
                                    <input type="file" name="attachment" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Note / Description') }}</label>
                                    <textarea name="note" id="edit_note" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Update Income') }}</span>
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
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.edit-income-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = this.dataset.id;
                    var form = document.getElementById('editIncomeForm');
                    form.action = "{{ url('admin/finance/income') }}/" + id;

                    document.getElementById('edit_account_id').value = this.dataset.accountId;
                    document.getElementById('edit_category_id').value = this.dataset.categoryId || '';
                    document.getElementById('edit_amount').value = this.dataset.amount;
                    document.getElementById('edit_date').value = this.dataset.date;
                    document.getElementById('edit_reference').value = this.dataset.reference || '';
                    document.getElementById('edit_payment_method').value = this.dataset.paymentMethod || 'Bank Transfer';
                    document.getElementById('edit_client_id').value = this.dataset.clientId || '';
                    document.getElementById('edit_project_id').value = this.dataset.projectId || '';
                    document.getElementById('edit_note').value = this.dataset.note || '';

                    var modal = new bootstrap.Modal(document.getElementById('editIncomeModal'));
                    modal.show();
                });
            });
        });
    </script>
@endpush
