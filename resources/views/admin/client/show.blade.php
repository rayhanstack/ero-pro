@extends('admin.layouts.app')

@section('title', $client->company_name)

@section('content')
    <div class="container-fluid py-3">
        <!-- Page Breadcrumb & Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ _trans('common.Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}" class="text-decoration-none">{{ _trans('common.Clients') }}</a></li>
                        <li class="breadcrumb-item active">{{ $client->company_name }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">{{ $client->company_name }}</h4>
                    <span class="badge bg-light text-muted border small">{{ $client->code }}</span>
                    <span class="badge {{ $client->status->badgeClass() }} rounded-pill px-2.5 py-1">
                        {{ $client->status->label() }}
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 d-flex align-items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ _trans('common.Back to Clients') }}</span>
                </a>
                @can('client.edit')
                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-primary btn-sm rounded-3 d-flex align-items-center gap-1.5">
                        <i class="bi bi-pencil"></i>
                        <span>{{ _trans('common.Edit Client') }}</span>
                    </a>
                @endcan
                @can('client.delete')
                    <form method="POST" action="{{ route('clients.destroy', $client) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this client?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Profile Hero Header Card -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $client->logo_url }}" class="rounded-circle object-fit-cover border shadow-xs flex-shrink-0" width="80" height="80" alt="{{ $client->company_name }}">
                        <div>
                            <h4 class="fw-bold mb-1 text-dark">{{ $client->company_name }}</h4>
                            <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                                <div><i class="bi bi-person me-1 text-primary"></i><strong>{{ $client->contact_name }}</strong></div>
                                @if ($client->email)
                                    <div><a href="mailto:{{ $client->email }}" class="text-decoration-none text-muted"><i class="bi bi-envelope me-1 text-primary"></i>{{ $client->email }}</a></div>
                                @endif
                                @if ($client->phone)
                                    <div><a href="tel:{{ $client->phone }}" class="text-decoration-none text-muted"><i class="bi bi-telephone me-1 text-success"></i>{{ $client->phone }}</a></div>
                                @endif
                                @if ($client->website)
                                    <div><a href="{{ $client->website }}" target="_blank" class="text-decoration-none text-primary"><i class="bi bi-globe me-1"></i>{{ $client->website }}</a></div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- KPI Stats Badges -->
                    <div class="d-flex flex-wrap gap-2 text-center">
                        <div class="p-2 px-3 bg-light rounded-3 border">
                            <div class="fs-5 fw-bold text-dark">{{ $client->projects->count() }}</div>
                            <div class="text-muted small" style="font-size: 11px;">{{ _trans('common.Projects') }}</div>
                        </div>
                        <div class="p-2 px-3 bg-light rounded-3 border">
                            <div class="fs-5 fw-bold text-dark">{{ $client->contacts->count() }}</div>
                            <div class="text-muted small" style="font-size: 11px;">{{ _trans('common.Contacts') }}</div>
                        </div>
                        <div class="p-2 px-3 bg-light rounded-3 border">
                            <div class="fs-5 fw-bold text-success">{{ currency_format($client->total_paid) }}</div>
                            <div class="text-muted small" style="font-size: 11px;">{{ _trans('common.Total Paid') }}</div>
                        </div>
                        <div class="p-2 px-3 bg-light rounded-3 border">
                            <div class="fs-5 fw-bold text-primary">{{ currency_format($client->total_invoiced) }}</div>
                            <div class="text-muted small" style="font-size: 11px;">{{ _trans('common.Total Invoiced') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="card-footer bg-light bg-opacity-50 border-top px-4 py-0">
                <ul class="nav nav-tabs border-0 gap-3" id="clientProfileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active border-0 border-bottom border-primary border-3 fw-semibold py-3 px-2 text-dark bg-transparent" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button" role="tab">
                            <i class="bi bi-info-circle me-1.5 text-primary"></i>{{ _trans('common.Overview') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 fw-semibold py-3 px-2 text-muted bg-transparent" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts-pane" type="button" role="tab">
                            <i class="bi bi-person-lines-fill me-1.5 text-primary"></i>{{ _trans('common.Contacts') }}
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1 small">{{ $client->contacts->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 fw-semibold py-3 px-2 text-muted bg-transparent" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects-pane" type="button" role="tab">
                            <i class="bi bi-folder2-open me-1.5 text-primary"></i>{{ _trans('common.Projects') }}
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1 small">{{ $client->projects->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 fw-semibold py-3 px-2 text-muted bg-transparent" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes-pane" type="button" role="tab">
                            <i class="bi bi-journal-text me-1.5 text-primary"></i>{{ _trans('common.Notes') }}
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1 small">{{ $client->clientNotes->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 fw-semibold py-3 px-2 text-muted bg-transparent" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices-pane" type="button" role="tab">
                            <i class="bi bi-receipt me-1.5 text-primary"></i>{{ _trans('common.Invoices & Payments') }}
                            <span class="badge bg-primary-subtle text-primary rounded-pill ms-1 small">{{ $client->invoices->count() }}</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab Panes Content -->
        <div class="tab-content" id="clientProfileTabsContent">
            <!-- 1. Overview Tab -->
            <div class="tab-pane fade show active" id="overview-pane" role="tabpanel">
                <div class="row g-4">
                    <!-- Left Column: Company & Contact Information -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                                <h6 class="fw-bold mb-0 text-dark">{{ _trans('common.Company Information') }}</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted small ps-0" style="width: 35%;">{{ _trans('common.Company Name') }}:</td>
                                            <td class="fw-semibold text-dark">{{ $client->company_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small ps-0">{{ _trans('common.Client Code') }}:</td>
                                            <td class="fw-semibold text-dark">{{ $client->code }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small ps-0">{{ _trans('common.Industry') }}:</td>
                                            <td class="text-dark">{{ $client->industry ?: '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small ps-0">{{ _trans('common.Currency') }}:</td>
                                            <td class="text-dark">
                                                @if ($client->currency)
                                                    <span class="badge bg-light text-dark border">{{ $client->currency->code }} ({{ $client->currency->symbol }})</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small ps-0">{{ _trans('common.Primary Contact') }}:</td>
                                            <td class="fw-semibold text-dark">{{ $client->contact_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small ps-0">{{ _trans('common.Email Address') }}:</td>
                                            <td class="text-dark"><a href="mailto:{{ $client->email }}" class="text-decoration-none">{{ $client->email }}</a></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small ps-0">{{ _trans('common.Phone Number') }}:</td>
                                            <td class="text-dark">{{ $client->phone ?: '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small ps-0">{{ _trans('common.Website') }}:</td>
                                            <td class="text-dark">
                                                @if ($client->website)
                                                    <a href="{{ $client->website }}" target="_blank" class="text-decoration-none">{{ $client->website }}</a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Location & Address & Notes -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                                <h6 class="fw-bold mb-0 text-dark">{{ _trans('common.Billing & Location') }}</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless align-middle mb-3">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted small ps-0" style="width: 35%;">{{ _trans('common.Country') }}:</td>
                                            <td class="fw-semibold text-dark">{{ $client->country?->name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small ps-0">{{ _trans('common.State / Province') }}:</td>
                                            <td class="text-dark">{{ $client->state?->name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small ps-0">{{ _trans('common.City') }}:</td>
                                            <td class="text-dark">{{ $client->city?->name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small ps-0">{{ _trans('common.Street Address') }}:</td>
                                            <td class="text-dark">{{ $client->address ?: '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                @if ($client->notes)
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="fw-bold text-dark small mb-1"><i class="bi bi-info-circle me-1 text-primary"></i>{{ _trans('common.Client Notes') }}</div>
                                        <div class="text-muted small">{{ $client->notes }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Contacts Tab -->
            <div class="tab-pane fade" id="contacts-pane" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0 text-dark">{{ _trans('common.Client Contact Persons') }}</h6>
                        @can('client.edit')
                            <button type="button" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#addContactModal">
                                <i class="bi bi-plus-lg"></i>
                                <span>{{ _trans('common.Add Contact') }}</span>
                            </button>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-3">{{ _trans('common.Contact Name') }}</th>
                                        <th>{{ _trans('common.Designation') }}</th>
                                        <th>{{ _trans('common.Email') }}</th>
                                        <th>{{ _trans('common.Phone') }}</th>
                                        <th>{{ _trans('common.Primary') }}</th>
                                        <th class="text-end pe-3">{{ _trans('common.Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($client->contacts as $contact)
                                        <tr>
                                            <td class="ps-3 fw-semibold text-dark">{{ $contact->name }}</td>
                                            <td class="text-muted small">{{ $contact->designation ?: '—' }}</td>
                                            <td>
                                                @if ($contact->email)
                                                    <a href="mailto:{{ $contact->email }}" class="text-decoration-none">{{ $contact->email }}</a>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($contact->phone)
                                                    <a href="tel:{{ $contact->phone }}" class="text-decoration-none text-dark">{{ $contact->phone }}</a>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($contact->is_primary)
                                                    <span class="badge bg-primary rounded-pill small">{{ _trans('common.Primary') }}</span>
                                                @else
                                                    <span class="badge bg-light text-muted border small">{{ _trans('common.Secondary') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                @can('client.edit')
                                                    <form method="POST" action="{{ route('clients.contacts.destroy', $contact) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this contact?') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="{{ _trans('common.Delete') }}">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                {{ _trans('common.No additional contacts listed.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Projects Tab -->
            <div class="tab-pane fade" id="projects-pane" role="tabpanel">
                @if($client->projects->count() > 0)
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted extra-small text-uppercase">
                                    <tr>
                                        <th class="ps-3">{{ _trans('common.Project Name') }}</th>
                                        <th>{{ _trans('common.Code') }}</th>
                                        <th>{{ _trans('common.Status') }}</th>
                                        <th>{{ _trans('common.Deadline') }}</th>
                                        <th class="text-end pe-3">{{ _trans('common.Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->projects as $proj)
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark">{{ $proj->name }}</td>
                                            <td><span class="badge bg-light text-muted border">{{ $proj->code }}</span></td>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1 rounded-pill">
                                                    {{ $proj->status?->label() ?? $proj->status }}
                                                </span>
                                            </td>
                                            <td class="small">{{ $proj->deadline ? $proj->deadline->format('M d, Y') : '—' }}</td>
                                            <td class="text-end pe-3">
                                                <a href="{{ route('projects.show', $proj->id) }}" class="btn btn-sm btn-light rounded-circle" title="{{ _trans('common.View Project') }}">
                                                    <i class="bi bi-eye text-primary"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center text-muted">
                        <i class="bi bi-folder2-open fs-1 d-block mb-3 text-secondary opacity-50"></i>
                        <h5 class="fw-semibold text-dark">{{ _trans('common.No active projects yet') }}</h5>
                        <p class="small text-muted mb-0">{{ _trans('common.Projects assigned to this client will appear here.') }}</p>
                    </div>
                @endif
            </div>

            <!-- 4. Notes Tab -->
            <div class="tab-pane fade" id="notes-pane" role="tabpanel">
                <div class="row g-4">
                    <!-- Add Note Form -->
                    @can('client.edit')
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                                <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-pencil-square me-1.5 text-primary"></i>{{ _trans('common.Add Note') }}</h6>
                                <form method="POST" action="{{ route('clients.notes.store', $client) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea name="note" class="form-control" rows="4" placeholder="{{ _trans('common.Write a note about this client...') }}" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3 shadow-sm">
                                        <i class="bi bi-check-lg me-1"></i>{{ _trans('common.Save Note') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endcan

                    <!-- Notes Timeline List -->
                    <div class="{{ auth()->user()->can('client.edit') ? 'col-lg-8' : 'col-12' }}">
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                            <h6 class="fw-bold mb-3 text-dark">{{ _trans('common.Notes History') }}</h6>
                            <div class="d-flex flex-column gap-3">
                                @forelse ($client->clientNotes as $note)
                                    <div class="p-3 bg-light rounded-3 border position-relative">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="fw-semibold text-dark small">{{ $note->user?->name ?? _trans('common.User') }}</div>
                                            <div class="text-muted small" style="font-size: 11px;">{{ $note->created_at->diffForHumans() }}</div>
                                        </div>
                                        <div class="text-dark small mb-0">{{ $note->note }}</div>

                                        @can('client.edit')
                                            <div class="text-end mt-1">
                                                <form method="POST" action="{{ route('clients.notes.destroy', $note) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this note?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link p-0 text-danger small text-decoration-none" style="font-size: 11px;">
                                                        <i class="bi bi-trash me-0.5"></i>{{ _trans('common.Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @endcan
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted small">
                                        <i class="bi bi-journal-x fs-2 d-block mb-1 text-secondary opacity-50"></i>
                                        {{ _trans('common.No notes recorded for this client.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Invoices & Payments Tab -->
            <div class="tab-pane fade" id="invoices-pane" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">{{ _trans('common.Invoices & Billing History') }}</h6>
                            <small class="text-muted">{{ _trans('common.Overview of all invoices issued to this client') }}</small>
                        </div>
                        @can('finance.create')
                            <a href="{{ route('finance.invoices.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-primary rounded-pill d-inline-flex align-items-center gap-1">
                                <i class="bi bi-plus-lg"></i>
                                <span>{{ _trans('common.New Invoice') }}</span>
                            </a>
                        @endcan
                    </div>
                    <div class="card-body p-4">
                        @if($client->invoices->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-muted extra-small text-uppercase">
                                        <tr>
                                            <th class="ps-3">{{ _trans('common.Invoice #') }}</th>
                                            <th>{{ _trans('common.Project') }}</th>
                                            <th>{{ _trans('common.Issue Date') }}</th>
                                            <th>{{ _trans('common.Due Date') }}</th>
                                            <th class="text-end">{{ _trans('common.Total') }}</th>
                                            <th class="text-end">{{ _trans('common.Paid') }}</th>
                                            <th class="text-end">{{ _trans('common.Due') }}</th>
                                            <th class="text-center">{{ _trans('common.Status') }}</th>
                                            <th class="text-end pe-3">{{ _trans('common.Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($client->invoices as $inv)
                                            <tr>
                                                <td class="ps-3">
                                                    <a href="{{ route('finance.invoices.show', $inv->id) }}" class="fw-bold text-primary text-decoration-none">
                                                        #{{ $inv->invoice_number }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if($inv->project)
                                                        <span class="badge bg-light text-dark border">{{ $inv->project->name }}</span>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td class="small">{{ $inv->issue_date ? $inv->issue_date->format('M d, Y') : '—' }}</td>
                                                <td class="small {{ $inv->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                                    {{ $inv->due_date ? $inv->due_date->format('M d, Y') : '—' }}
                                                </td>
                                                <td class="text-end fw-bold text-dark">{{ currency_format($inv->total_amount) }}</td>
                                                <td class="text-end text-success fw-semibold">{{ currency_format($inv->paid_amount) }}</td>
                                                <td class="text-end fw-bold {{ $inv->due_amount > 0 ? 'text-danger' : 'text-muted' }}">{{ currency_format($inv->due_amount) }}</td>
                                                <td class="text-center">
                                                    <span class="badge {{ $inv->status->badgeClass() }} px-2.5 py-1 rounded-pill extra-small">
                                                        {{ $inv->status->label() }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <a href="{{ route('finance.invoices.show', $inv->id) }}" class="btn btn-sm btn-icon btn-light rounded-circle" title="{{ _trans('common.View Invoice') }}">
                                                            <i class="bi bi-eye text-primary"></i>
                                                        </a>
                                                        <a href="{{ route('finance.invoices.download-pdf', $inv->id) }}" class="btn btn-sm btn-icon btn-light rounded-circle" title="{{ _trans('common.Download PDF') }}" target="_blank">
                                                            <i class="bi bi-file-earmark-pdf text-danger"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                <h5 class="fw-semibold text-dark">{{ _trans('common.No invoices found for this client') }}</h5>
                                <p class="small text-muted mb-3">{{ _trans('common.Generate invoices to record billables and payments for this client.') }}</p>
                                @can('finance.create')
                                    <a href="{{ route('finance.invoices.create', ['client_id' => $client->id]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-plus-lg me-1"></i>{{ _trans('common.Create First Invoice') }}
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Contact Modal -->
    @can('client.edit')
        <div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST" action="{{ route('clients.contacts.store', $client) }}">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold text-dark" id="addContactModalLabel">
                                <i class="bi bi-person-plus text-primary me-2"></i>{{ _trans('common.Add Contact Person') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Full Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Jane Doe" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Designation / Role') }}</label>
                                    <input type="text" name="designation" class="form-control" placeholder="e.g. Project Manager, Technical Lead">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Email') }}</label>
                                    <input type="email" name="email" class="form-control" placeholder="contact@client.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Phone') }}</label>
                                    <input type="text" name="phone" class="form-control" placeholder="+1 555-0188">
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="isPrimaryCheck">
                                        <label class="form-check-label text-dark small" for="isPrimaryCheck">
                                            {{ _trans('common.Set as primary contact') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                                <i class="bi bi-check-lg me-1"></i>{{ _trans('common.Save Contact') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection
