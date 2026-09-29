@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Accounts & Live Balances'))

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Accounts & Live Balances') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Manage bank accounts, cash drawers, mobile wallets, and inter-account transfers') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @can('finance.create')
                <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#transferModal">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>{{ _trans('common.Transfer Funds') }}</span>
                </button>

                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newAccountModal">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.New Account') }}</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- Live Balances Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Total Net Balance') }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ currency_format($summary['total_balance']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-bank2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Bank Accounts') }}</div>
                        <h4 class="fw-bold mb-0 text-info">{{ currency_format($summary['bank_balance']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Cash in Hand') }}</div>
                        <h4 class="fw-bold mb-0 text-success">{{ currency_format($summary['cash_balance']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-phone fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-medium">{{ _trans('common.Mobile Wallets') }}</div>
                        <h4 class="fw-bold mb-0 text-warning">{{ currency_format($summary['mobile_balance']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Accounts Table Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="card-header bg-white border-0 py-3 px-4">
            <form method="GET" action="{{ route('finance.accounts.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4 col-lg-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="{{ _trans('common.Search accounts...') }}" value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>

                <div class="col-md-3 col-lg-2">
                    <select name="type" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">{{ _trans('common.All Types') }}</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" {{ ($filters['type'] ?? '') === $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-lg-2">
                    <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">{{ _trans('common.All Statuses') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    @if (! empty($filters['search']) || ! empty($filters['type']) || ! empty($filters['status']))
                        <a href="{{ route('finance.accounts.index') }}" class="btn btn-light" title="{{ _trans('common.Reset Filters') }}">
                            <i class="bi bi-x-circle me-1"></i>{{ _trans('common.Reset') }}
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
                        <th class="ps-4">{{ _trans('common.Account Name & Details') }}</th>
                        <th>{{ _trans('common.Type') }}</th>
                        <th>{{ _trans('common.Opening Balance') }}</th>
                        <th>{{ _trans('common.Live Balance') }}</th>
                        <th>{{ _trans('common.Status') }}</th>
                        <th class="text-end pe-4">{{ _trans('common.Action') }}</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse ($accounts as $acc)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle {{ $acc->type->badgeClass() }} p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi {{ $acc->type->icon() }} fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $acc->name }}</div>
                                        <div class="text-muted extra-small">
                                            @if ($acc->account_number)
                                                <span>{{ $acc->account_number }}</span>
                                            @endif
                                            @if ($acc->bank_name)
                                                <span>• {{ $acc->bank_name }}</span>
                                            @endif
                                            @if ($acc->branch)
                                                <span>({{ $acc->branch }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $acc->type->badgeClass() }} px-2 py-1">
                                    <i class="bi {{ $acc->type->icon() }} me-1"></i>{{ $acc->type->label() }}
                                </span>
                            </td>
                            <td class="text-muted">{{ currency_format($acc->opening_balance) }}</td>
                            <td>
                                <span class="fs-6 fw-bold {{ $acc->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ currency_format($acc->balance) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $acc->status->badgeClass() }} px-2 py-1">
                                    {{ $acc->status->label() }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @can('finance.edit')
                                        <button type="button" class="btn btn-sm btn-light text-primary edit-account-btn"
                                            data-id="{{ $acc->id }}"
                                            data-name="{{ $acc->name }}"
                                            data-type="{{ $acc->type->value }}"
                                            data-account-number="{{ $acc->account_number }}"
                                            data-bank-name="{{ $acc->bank_name }}"
                                            data-branch="{{ $acc->branch }}"
                                            data-opening-balance="{{ $acc->opening_balance }}"
                                            data-status="{{ $acc->status->value }}"
                                            data-note="{{ $acc->note }}"
                                            title="{{ _trans('common.Edit Account') }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endcan

                                    @can('finance.delete')
                                        <form method="POST" action="{{ route('finance.accounts.destroy', $acc) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this account?') }}')">
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
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-bank display-6 d-block mb-2 text-secondary opacity-50"></i>
                                {{ _trans('common.No accounts found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($accounts->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $accounts->links() }}
            </div>
        @endif
    </div>

    {{-- Create Account Modal --}}
    @can('finance.create')
        <div class="modal fade" id="newAccountModal" tabindex="-1" aria-labelledby="newAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="{{ route('finance.accounts.store') }}">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold" id="newAccountModalLabel">
                                <i class="bi bi-bank2 text-primary me-2"></i>{{ _trans('common.Create New Account') }}
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
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Account Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. City Bank - Corporate Account" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Account Type') }} <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Opening Balance') }}</label>
                                    <input type="number" step="0.01" name="opening_balance" class="form-control" value="0.00">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Account Number') }}</label>
                                    <input type="text" name="account_number" class="form-control" placeholder="e.g. 110-234-5678">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Bank Name') }}</label>
                                    <input type="text" name="bank_name" class="form-control" placeholder="e.g. City Bank PLC">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Branch Name') }}</label>
                                    <input type="text" name="branch" class="form-control" placeholder="e.g. Gulshan Branch">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Notes / Description') }}</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="{{ _trans('common.Optional notes about this account...') }}"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Save Account') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit Account Modal --}}
        <div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" id="editAccountForm" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold" id="editAccountModalLabel">
                                <i class="bi bi-pencil-square text-primary me-2"></i>{{ _trans('common.Edit Account') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Account Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Account Type') }} <span class="text-danger">*</span></label>
                                    <select name="type" id="edit_type" class="form-select" required>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                                    <select name="status" id="edit_status" class="form-select" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Opening Balance') }}</label>
                                    <input type="number" step="0.01" name="opening_balance" id="edit_opening_balance" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Account Number') }}</label>
                                    <input type="text" name="account_number" id="edit_account_number" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Bank Name') }}</label>
                                    <input type="text" name="bank_name" id="edit_bank_name" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Branch Name') }}</label>
                                    <input type="text" name="branch" id="edit_branch" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Notes / Description') }}</label>
                                    <textarea name="note" id="edit_note" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Update Account') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Fund Transfer Modal --}}
        <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="{{ route('finance.accounts.transfer') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold" id="transferModalLabel">
                                <i class="bi bi-arrow-left-right text-primary me-2"></i>{{ _trans('common.Transfer Funds Between Accounts') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.From Account (Source)') }} <span class="text-danger">*</span></label>
                                    <select name="from_account_id" class="form-select" required>
                                        <option value="">{{ _trans('common.Select Source Account') }}</option>
                                        @foreach ($allActiveAccounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ currency_format($acc->balance) }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.To Account (Destination)') }} <span class="text-danger">*</span></label>
                                    <select name="to_account_id" class="form-select" required>
                                        <option value="">{{ _trans('common.Select Destination Account') }}</option>
                                        @foreach ($allActiveAccounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Transfer Amount') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Transfer Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Reference / Cheque No') }}</label>
                                    <input type="text" name="reference" class="form-control" placeholder="e.g. TRF-98231">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Attachment') }}</label>
                                    <input type="file" name="attachment" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">{{ _trans('common.Note / Reason') }}</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="{{ _trans('common.Reason for transfer...') }}"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Execute Transfer') }}</span>
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
            // Edit Account Modal populate
            document.querySelectorAll('.edit-account-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = this.dataset.id;
                    var form = document.getElementById('editAccountForm');
                    form.action = "{{ url('admin/finance/accounts') }}/" + id;

                    document.getElementById('edit_name').value = this.dataset.name;
                    document.getElementById('edit_type').value = this.dataset.type;
                    document.getElementById('edit_status').value = this.dataset.status;
                    document.getElementById('edit_opening_balance').value = this.dataset.openingBalance;
                    document.getElementById('edit_account_number').value = this.dataset.accountNumber || '';
                    document.getElementById('edit_bank_name').value = this.dataset.bankName || '';
                    document.getElementById('edit_branch').value = this.dataset.branch || '';
                    document.getElementById('edit_note').value = this.dataset.note || '';

                    var modal = new bootstrap.Modal(document.getElementById('editAccountModal'));
                    modal.show();
                });
            });
        });
    </script>
@endpush
