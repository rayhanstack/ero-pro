@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Create Invoice'))

@section('content')
    <div class="mb-4">
        <a href="{{ route('finance.invoices.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i>
            <span>{{ _trans('common.Back to Invoices') }}</span>
        </a>
        <h3 class="fw-bold mb-1">{{ _trans('common.Create New Invoice') }}</h3>
        <p class="text-muted small mb-0">{{ _trans('common.Generate an itemized invoice for a client with milestones or services') }}</p>
    </div>

    <form action="{{ route('finance.invoices.store') }}" method="POST" id="invoiceForm">
        @csrf

        <div class="row g-4">
            {{-- Left Column: Invoice Details & Items --}}
            <div class="col-lg-8">
                {{-- Header Details Card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold mb-0 text-dark">{{ _trans('common.Invoice Information') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">{{ _trans('common.Client') }} <span class="text-danger">*</span></label>
                                <select name="client_id" id="client_select" class="form-select select2 rounded-3" required>
                                    <option value="">{{ _trans('common.Select Client') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id', request('client_id')) == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }} ({{ $client->company_name ?: $client->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <div class="text-danger extra-small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">{{ _trans('common.Project (Optional)') }}</label>
                                <select name="project_id" id="project_select" class="form-select select2 rounded-3">
                                    <option value="">{{ _trans('common.None / General Invoice') }}</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" data-client="{{ $project->client_id }}" {{ old('project_id', request('project_id')) == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }} ({{ $project->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="text-danger extra-small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">{{ _trans('common.Invoice Number') }}</label>
                                <input type="text" name="invoice_number" class="form-control rounded-3" value="{{ old('invoice_number', $nextInvoiceNumber) }}" placeholder="INV-202609-0001">
                                <span class="text-muted extra-small">{{ _trans('common.Leave empty to auto-generate') }}</span>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">{{ _trans('common.Issue Date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date" class="form-control rounded-3" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                                @error('issue_date')
                                    <div class="text-danger extra-small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">{{ _trans('common.Due Date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control rounded-3" value="{{ old('due_date', date('Y-m-d', strtotime('+15 days'))) }}" required>
                                @error('due_date')
                                    <div class="text-danger extra-small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Line Items Card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ _trans('common.Invoice Items') }}</h5>
                            <small class="text-muted">{{ _trans('common.Add product or service line items with quantities and rates') }}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-inline-flex align-items-center gap-1" id="addItemBtn">
                            <i class="bi bi-plus-lg"></i>
                            <span>{{ _trans('common.Add Item') }}</span>
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle" id="itemsTable">
                                <thead class="table-light text-muted extra-small text-uppercase">
                                    <tr>
                                        <th style="width: 45%;">{{ _trans('common.Item / Description') }} <span class="text-danger">*</span></th>
                                        <th style="width: 15%;">{{ _trans('common.Qty') }} <span class="text-danger">*</span></th>
                                        <th style="width: 20%;">{{ _trans('common.Unit Price') }} <span class="text-danger">*</span></th>
                                        <th style="width: 15%;" class="text-end">{{ _trans('common.Amount') }}</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr class="item-row">
                                        <td>
                                            <input type="text" name="items[0][item_name]" class="form-control rounded-3 item-name" placeholder="{{ _trans('common.e.g. Milestone 1 - UI/UX Design') }}" required>
                                            <input type="text" name="items[0][description]" class="form-control rounded-3 form-control-sm text-muted mt-1 item-desc" placeholder="{{ _trans('common.Optional description') }}">
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control rounded-3 item-qty" value="1" min="0.01" step="any" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][unit_price]" class="form-control rounded-3 item-price" value="0.00" min="0" step="0.01" required>
                                        </td>
                                        <td class="text-end fw-bold text-dark item-total">
                                            0.00
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-light text-danger rounded-circle remove-item-btn" disabled>
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Notes & Terms Card --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">{{ _trans('common.Client Notes') }}</label>
                                <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="{{ _trans('common.Notes or bank payment instructions visible to client...') }}">{{ old('notes') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">{{ _trans('common.Terms & Conditions') }}</label>
                                <textarea name="terms" class="form-control rounded-3" rows="3" placeholder="{{ _trans('common.Payment due within 15 days. Late payment fee applies...') }}">{{ old('terms', 'Payment is due within 15 days from issue date. Please remit payment via Bank Transfer or Mobile Wallet.') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Summary & Actions --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold mb-0 text-dark">{{ _trans('common.Invoice Summary') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">{{ _trans('common.Subtotal') }}</span>
                            <span class="fw-bold text-dark" id="displaySubtotal">0.00</span>
                        </div>

                        <div class="py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="text-muted small mb-0">{{ _trans('common.Discount') }}</label>
                                <span class="fw-semibold text-danger" id="displayDiscount">-0.00</span>
                            </div>
                            <div class="input-group input-group-sm">
                                <input type="number" name="discount" id="discountInput" class="form-control rounded-start-3" value="{{ old('discount', '0.00') }}" min="0" step="0.01">
                                <span class="input-group-text bg-light rounded-end-3">{{ currency_symbol() }}</span>
                            </div>
                        </div>

                        <div class="py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="text-muted small mb-0">{{ _trans('common.Tax / VAT') }}</label>
                                <span class="fw-semibold text-dark" id="displayTax">+0.00</span>
                            </div>
                            <div class="input-group input-group-sm">
                                <input type="number" name="tax" id="taxInput" class="form-control rounded-start-3" value="{{ old('tax', '0.00') }}" min="0" step="0.01">
                                <span class="input-group-text bg-light rounded-end-3">{{ currency_symbol() }}</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 mb-4">
                            <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Grand Total') }}</h5>
                            <h4 class="fw-bold text-primary mb-0" id="displayTotal">0.00</h4>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted">{{ _trans('common.Initial Status') }}</label>
                            <select name="status" class="form-select rounded-3">
                                @foreach($statuses as $status)
                                    @if($status->value !== 'partial' && $status->value !== 'paid')
                                        <option value="{{ $status->value }}" {{ old('status') === $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Save & Create Invoice') }}</span>
                            </button>
                            <a href="{{ route('finance.invoices.index') }}" class="btn btn-light rounded-3">
                                {{ _trans('common.Cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let itemIndex = 1;

        // Auto-filter projects based on client selection
        $('#client_select').on('change', function() {
            const clientId = $(this).val();
            $('#project_select option').each(function() {
                const projectClient = $(this).data('client');
                if (!projectClient || projectClient == clientId || !clientId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Add line item
        $('#addItemBtn').on('click', function() {
            const rowHtml = `
                <tr class="item-row">
                    <td>
                        <input type="text" name="items[${itemIndex}][item_name]" class="form-control rounded-3 item-name" placeholder="{{ _trans('common.Item or service description') }}" required>
                        <input type="text" name="items[${itemIndex}][description]" class="form-control rounded-3 form-control-sm text-muted mt-1 item-desc" placeholder="{{ _trans('common.Optional description') }}">
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control rounded-3 item-qty" value="1" min="0.01" step="any" required>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][unit_price]" class="form-control rounded-3 item-price" value="0.00" min="0" step="0.01" required>
                    </td>
                    <td class="text-end fw-bold text-dark item-total">
                        0.00
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-light text-danger rounded-circle remove-item-btn">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#itemsBody').append(rowHtml);
            itemIndex++;
            updateRemoveButtons();
            recalculateInvoice();
        });

        // Remove line item
        $(document).on('click', '.remove-item-btn', function() {
            if ($('.item-row').length > 1) {
                $(this).closest('tr').remove();
                updateRemoveButtons();
                recalculateInvoice();
            }
        });

        function updateRemoveButtons() {
            if ($('.item-row').length <= 1) {
                $('.remove-item-btn').prop('disabled', true);
            } else {
                $('.remove-item-btn').prop('disabled', false);
            }
        }

        // Live calculation triggers
        $(document).on('input', '.item-qty, .item-price, #discountInput, #taxInput', function() {
            recalculateInvoice();
        });

        function recalculateInvoice() {
            let subtotal = 0;

            $('.item-row').each(function() {
                const qty = parseFloat($(this).find('.item-qty').val()) || 0;
                const price = parseFloat($(this).find('.item-price').val()) || 0;
                const lineTotal = qty * price;
                $(this).find('.item-total').text(lineTotal.toFixed(2));
                subtotal += lineTotal;
            });

            const discount = parseFloat($('#discountInput').val()) || 0;
            const tax = parseFloat($('#taxInput').val()) || 0;
            const grandTotal = Math.max(0, subtotal - discount + tax);

            $('#displaySubtotal').text(subtotal.toFixed(2));
            $('#displayDiscount').text('-' + discount.toFixed(2));
            $('#displayTax').text('+' + tax.toFixed(2));
            $('#displayTotal').text(grandTotal.toFixed(2));
        }

        recalculateInvoice();
    });
</script>
@endpush
