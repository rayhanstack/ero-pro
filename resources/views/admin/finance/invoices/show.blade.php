@extends('admin.layouts.app')
@section('title', ($title ?? _trans('common.Invoice')) . ' #' . $invoice->invoice_number)

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="{{ route('finance.invoices.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Invoices') }}</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <h3 class="fw-bold mb-0">#{{ $invoice->invoice_number }}</h3>
                <span class="badge {{ $invoice->status->badgeClass() }} px-3 py-1.5 rounded-pill fs-6">
                    {{ $invoice->status->label() }}
                </span>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('finance.invoices.download-pdf', $invoice->id) }}" class="btn btn-outline-danger d-inline-flex align-items-center gap-1.5" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i>
                <span>{{ _trans('common.Download PDF') }}</span>
            </a>

            @if($invoice->status->value !== 'paid')
                @can('finance.create')
                    <button type="button" class="btn btn-success d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                        <i class="bi bi-cash-stack"></i>
                        <span>{{ _trans('common.Record Payment') }}</span>
                    </button>
                @endcan

                @can('finance.edit')
                    <a href="{{ route('finance.invoices.edit', $invoice->id) }}" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-pencil"></i>
                        <span>{{ _trans('common.Edit Invoice') }}</span>
                    </a>
                @endcan
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Invoice Body (70%) --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    {{-- Top Company & Invoice Meta --}}
                    <div class="row justify-content-between align-items-start border-bottom pb-4 mb-4">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-primary mb-1">ERP Pro</h4>
                            <p class="text-muted small mb-0">
                                Enterprise Resource Planning & Finance<br>
                                Dhaka, Bangladesh<br>
                                info@erp.test
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <h3 class="fw-bold text-dark mb-1 text-uppercase">{{ _trans('common.INVOICE') }}</h3>
                            <div class="text-muted small"><strong>{{ _trans('common.Invoice #') }}:</strong> {{ $invoice->invoice_number }}</div>
                            <div class="text-muted small"><strong>{{ _trans('common.Issue Date') }}:</strong> {{ $invoice->issue_date ? $invoice->issue_date->format('F d, Y') : '—' }}</div>
                            <div class="text-muted small"><strong>{{ _trans('common.Due Date') }}:</strong> <span class="{{ $invoice->isOverdue() ? 'text-danger fw-bold' : '' }}">{{ $invoice->due_date ? $invoice->due_date->format('F d, Y') : '—' }}</span></div>
                        </div>
                    </div>

                    {{-- Bill To & Project Info --}}
                    <div class="row justify-content-between mb-4">
                        <div class="col-md-6">
                            <span class="text-muted extra-small text-uppercase fw-bold">{{ _trans('common.Billed To') }}:</span>
                            @if($invoice->client)
                                <h6 class="fw-bold text-dark mt-1 mb-0">{{ $invoice->client->name }}</h6>
                                @if($invoice->client->company_name)
                                    <div class="text-muted small">{{ $invoice->client->company_name }}</div>
                                @endif
                                <div class="text-muted small">{{ $invoice->client->email }}</div>
                                @if($invoice->client->phone)
                                    <div class="text-muted small">{{ $invoice->client->phone }}</div>
                                @endif
                                @if($invoice->client->address)
                                    <div class="text-muted small">{{ $invoice->client->address }}</div>
                                @endif
                            @else
                                <div class="text-muted small">—</div>
                            @endif
                        </div>

                        <div class="col-md-5 text-md-end mt-3 mt-md-0">
                            @if($invoice->project)
                                <span class="text-muted extra-small text-uppercase fw-bold">{{ _trans('common.Project Reference') }}:</span>
                                <h6 class="fw-bold text-dark mt-1 mb-0">{{ $invoice->project->name }}</h6>
                                <div class="text-muted small">{{ _trans('common.Code') }}: {{ $invoice->project->code }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Line Items Table --}}
                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead class="table-light text-muted extra-small text-uppercase">
                                <tr>
                                    <th class="ps-3" style="width: 50%;">{{ _trans('common.Description') }}</th>
                                    <th class="text-center" style="width: 15%;">{{ _trans('common.Quantity') }}</th>
                                    <th class="text-end" style="width: 15%;">{{ _trans('common.Unit Price') }}</th>
                                    <th class="text-end pe-3" style="width: 20%;">{{ _trans('common.Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                            @if($item->description)
                                                <small class="text-muted">{{ $item->description }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ (float)$item->quantity }}</td>
                                        <td class="text-end">{{ currency_format($item->unit_price) }}</td>
                                        <td class="text-end pe-3 fw-bold text-dark">{{ currency_format($item->total_price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Calculation Summary --}}
                    <div class="row justify-content-end mb-4">
                        <div class="col-md-6 col-lg-5">
                            <div class="d-flex justify-content-between py-1.5 border-bottom">
                                <span class="text-muted">{{ _trans('common.Subtotal') }}</span>
                                <span class="fw-semibold text-dark">{{ currency_format($invoice->subtotal) }}</span>
                            </div>
                            @if($invoice->discount > 0)
                                <div class="d-flex justify-content-between py-1.5 border-bottom text-danger">
                                    <span>{{ _trans('common.Discount') }}</span>
                                    <span>-{{ currency_format($invoice->discount) }}</span>
                                </div>
                            @endif
                            @if($invoice->tax > 0)
                                <div class="d-flex justify-content-between py-1.5 border-bottom">
                                    <span class="text-muted">{{ _trans('common.Tax') }}</span>
                                    <span class="fw-semibold text-dark">+{{ currency_format($invoice->tax) }}</span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Total') }}</h5>
                                <h5 class="fw-bold text-primary mb-0">{{ currency_format($invoice->total_amount) }}</h5>
                            </div>
                            <div class="d-flex justify-content-between py-1.5 border-bottom text-success">
                                <span>{{ _trans('common.Paid Amount') }}</span>
                                <span class="fw-bold">{{ currency_format($invoice->paid_amount) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1.5 {{ $invoice->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                <span class="fw-bold">{{ _trans('common.Balance Due') }}</span>
                                <span class="fw-bold">{{ currency_format($invoice->due_amount) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Notes & Terms --}}
                    @if($invoice->notes || $invoice->terms)
                        <div class="border-top pt-4 mt-2">
                            @if($invoice->notes)
                                <div class="mb-3">
                                    <h6 class="fw-bold text-dark small mb-1">{{ _trans('common.Notes') }}</h6>
                                    <p class="text-muted small mb-0">{{ $invoice->notes }}</p>
                                </div>
                            @endif
                            @if($invoice->terms)
                                <div>
                                    <h6 class="fw-bold text-dark small mb-1">{{ _trans('common.Terms & Conditions') }}</h6>
                                    <p class="text-muted small mb-0">{{ $invoice->terms }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar: Payments & Financial Logs (30%) --}}
        <div class="col-lg-4">
            {{-- Payment History Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ _trans('common.Payment History') }}</h5>
                        <small class="text-muted">{{ _trans('common.Recorded client installments') }}</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    @forelse($invoice->payments as $payment)
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-success fs-6">{{ currency_format($payment->amount) }}</span>
                                <span class="badge bg-secondary rounded-pill extra-small">{{ $payment->payment_method }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted extra-small">
                                <span>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : '—' }}</span>
                                <span>{{ $payment->account?->name ?? 'Account' }}</span>
                            </div>
                            @if($payment->transaction_id)
                                <div class="mt-2 text-muted extra-small border-top pt-1">
                                    <i class="bi bi-link-45deg"></i> {{ _trans('common.Ref') }}: {{ $payment->reference ?: 'Auto-generated' }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-clock-history fs-3 d-block opacity-50 mb-1"></i>
                            <span class="small">{{ _trans('common.No payments recorded yet.') }}</span>
                        </div>
                    @endforelse

                    @if($invoice->due_amount > 0)
                        @can('finance.create')
                            <button type="button" class="btn btn-outline-success w-100 rounded-3 mt-2 d-flex align-items-center justify-content-center gap-1.5" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                                <i class="bi bi-plus-lg"></i>
                                <span>{{ _trans('common.Record Payment') }}</span>
                            </button>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Record Payment Modal --}}
    <div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form action="{{ route('finance.invoices.payments.store', $invoice->id) }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold text-dark">{{ _trans('common.Record Invoice Payment') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>{{ _trans('common.Invoice Total') }}:</span>
                                <span class="fw-semibold text-dark">{{ currency_format($invoice->total_amount) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span>{{ _trans('common.Current Balance Due') }}:</span>
                                <span class="fw-bold text-danger">{{ currency_format($invoice->due_amount) }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ _trans('common.Payment Amount') }} ({{ currency_symbol() }}) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control rounded-3" value="{{ old('amount', $invoice->due_amount) }}" max="{{ $invoice->due_amount }}" min="0.01" step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ _trans('common.Deposit Account') }} <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select rounded-3" required>
                                <option value="">{{ _trans('common.Select Account') }}</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ currency_format($acc->balance) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">{{ _trans('common.Payment Date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control rounded-3" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">{{ _trans('common.Payment Method') }} <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select rounded-3" required>
                                    <option value="Bank Transfer">{{ _trans('common.Bank Transfer') }}</option>
                                    <option value="Cash">{{ _trans('common.Cash') }}</option>
                                    <option value="bKash / Mobile">{{ _trans('common.bKash / Mobile') }}</option>
                                    <option value="Credit Card">{{ _trans('common.Credit Card') }}</option>
                                    <option value="Cheque">{{ _trans('common.Cheque') }}</option>
                                    <option value="Other">{{ _trans('common.Other') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ _trans('common.Reference / TxID') }}</label>
                            <input type="text" name="reference" class="form-control rounded-3" placeholder="{{ _trans('common.e.g. Bank slip #, bKash TrxID') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ _trans('common.Note / Remarks') }}</label>
                            <textarea name="note" class="form-control rounded-3" rows="2" placeholder="{{ _trans('common.Optional notes regarding payment') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn btn-success rounded-3 px-4">{{ _trans('common.Confirm & Record Payment') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
