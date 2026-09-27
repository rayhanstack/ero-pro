@extends('admin.layouts.app')

@section('title', _trans('common.Clients'))

@section('content')
    <!-- Stat Cards (Preserving exact original styling) -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white h-100 p-3 mb-0 shadow-sm border-0 rounded-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">{{ _trans('common.Total Clients') }}</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-people"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white h-100 p-3 mb-0 shadow-sm border-0 rounded-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">{{ _trans('common.Active Clients') }}</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['active'] }}</h3>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-person-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white h-100 p-3 mb-0 shadow-sm border-0 rounded-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">{{ _trans('common.New This Month') }}</h6>
                        <h3 class="fw-bold mb-0">+{{ $stats['new_this_month'] }}</h3>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-person-plus"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-dark h-100 p-3 mb-0 shadow-sm border-0 rounded-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-dark text-opacity-75">{{ _trans('common.Total Revenue') }}</h6>
                        <h3 class="fw-bold mb-0">${{ number_format($stats['total_revenue'], 2) }}</h3>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-currency-dollar"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('clients.index') }}" class="row g-3 align-items-center">
                <input type="hidden" name="view" value="{{ $viewMode }}">

                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="{{ _trans('common.Search clients by name, code, email...') }}" value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ _trans('common.Status: All') }}</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ ($filters['status'] ?? '') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 d-flex justify-content-md-end gap-2">
                    <!-- Grid / List View Toggle -->
                    <div class="btn-group" role="group">
                        <a href="{{ route('clients.index', array_merge($filters, ['view' => 'grid'])) }}" class="btn btn-sm {{ $viewMode === 'grid' ? 'btn-primary' : 'btn-outline-secondary' }}" title="{{ _trans('common.Grid View') }}">
                            <i class="bi bi-grid-fill"></i>
                        </a>
                        <a href="{{ route('clients.index', array_merge($filters, ['view' => 'list'])) }}" class="btn btn-sm {{ $viewMode === 'list' ? 'btn-primary' : 'btn-outline-secondary' }}" title="{{ _trans('common.List View') }}">
                            <i class="bi bi-list-ul"></i>
                        </a>
                    </div>

                    @can('client.create')
                        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#createClientModal">
                            <i class="bi bi-plus-lg"></i>
                            <span>{{ _trans('common.Add Client') }}</span>
                        </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>

    <!-- Client Content: Grid or List -->
    @if ($viewMode === 'grid')
        <!-- Client Grid -->
        <div class="row g-4">
            @forelse ($clients as $client)
                <div class="col-xl-3 col-md-6">
                    <div class="card h-100 text-center border-0 shadow-sm rounded-4 bg-white hover-shadow transition-all position-relative">
                        <div class="card-body pt-4">
                            <!-- Status Badge -->
                            <span class="badge {{ $client->status->badgeClass() }} rounded-pill position-absolute top-0 end-0 m-3">
                                {{ $client->status->label() }}
                            </span>

                            <!-- Client Logo -->
                            <img src="{{ $client->logo_url }}" class="rounded-circle mb-3 object-fit-cover shadow-sm border" width="64" height="64" alt="{{ $client->company_name }}">

                            <!-- Company Name & Code -->
                            <h5 class="fw-bold mb-1 text-dark text-truncate" title="{{ $client->company_name }}">
                                <a href="{{ route('clients.show', $client) }}" class="text-decoration-none text-dark">{{ $client->company_name }}</a>
                            </h5>
                            <p class="text-muted small mb-1">{{ $client->contact_name ?: _trans('common.Primary Contact') }}</p>
                            <span class="badge bg-light text-muted border small mb-3" style="font-size: 10px;">{{ $client->code }}</span>

                            <!-- Quick Action Buttons -->
                            <div class="d-flex justify-content-center gap-2 mb-4">
                                @if ($client->email)
                                    <a href="mailto:{{ $client->email }}" class="btn btn-sm btn-light rounded-circle border shadow-xs" title="{{ $client->email }}">
                                        <i class="bi bi-envelope text-primary"></i>
                                    </a>
                                @endif
                                @if ($client->phone)
                                    <a href="tel:{{ $client->phone }}" class="btn btn-sm btn-light rounded-circle border shadow-xs" title="{{ $client->phone }}">
                                        <i class="bi bi-telephone text-success"></i>
                                    </a>
                                @endif
                                @if ($client->website)
                                    <a href="{{ $client->website }}" target="_blank" class="btn btn-sm btn-light rounded-circle border shadow-xs" title="{{ $client->website }}">
                                        <i class="bi bi-globe text-info"></i>
                                    </a>
                                @endif
                            </div>

                            <!-- Summary Row (Projects & Revenue) -->
                            <div class="row text-center border-top border-bottom py-2 mx-0 mb-3 bg-light rounded-3">
                                <div class="col-6 border-end">
                                    <h6 class="fw-bold mb-0 text-dark">0</h6>
                                    <span class="small text-muted">{{ _trans('common.Projects') }}</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="fw-bold mb-0 text-dark">$0.00</h6>
                                    <span class="small text-muted">{{ _trans('common.Revenue') }}</span>
                                </div>
                            </div>

                            <!-- View Profile Button -->
                            <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-primary w-100 rounded-3">
                                {{ _trans('common.View Profile') }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center text-muted">
                        <i class="bi bi-people fs-1 d-block mb-3 text-secondary opacity-50"></i>
                        <h5 class="fw-semibold">{{ _trans('common.No clients found') }}</h5>
                        <p class="small mb-3">{{ _trans('common.Start by adding your first client.') }}</p>
                        @can('client.create')
                            <div>
                                <button type="button" class="btn btn-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#createClientModal">
                                    <i class="bi bi-plus-lg me-1"></i>{{ _trans('common.Add Client') }}
                                </button>
                            </div>
                        @endcan
                    </div>
                </div>
            @endforelse
        </div>
    @else
        <!-- Client Table View -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-3">{{ _trans('common.Client / Company') }}</th>
                                <th>{{ _trans('common.Primary Contact') }}</th>
                                <th>{{ _trans('common.Contact Info') }}</th>
                                <th>{{ _trans('common.Location / Industry') }}</th>
                                <th>{{ _trans('common.Status') }}</th>
                                <th class="text-end pe-3">{{ _trans('common.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clients as $client)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <img src="{{ $client->logo_url }}" class="rounded-circle object-fit-cover border" width="40" height="40" alt="{{ $client->company_name }}">
                                            <div>
                                                <a href="{{ route('clients.show', $client) }}" class="fw-semibold text-dark text-decoration-none">
                                                    {{ $client->company_name }}
                                                </a>
                                                <div class="text-muted small" style="font-size: 11px;">{{ $client->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $client->contact_name }}</div>
                                    </td>
                                    <td>
                                        <div class="small text-dark">{{ $client->email }}</div>
                                        @if ($client->phone)
                                            <div class="text-muted small" style="font-size: 11px;">{{ $client->phone }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-dark">{{ $client->city?->name ?? ($client->country?->name ?? '—') }}</div>
                                        @if ($client->industry)
                                            <div class="text-muted small" style="font-size: 11px;">{{ $client->industry }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $client->status->badgeClass() }} rounded-pill px-2.5 py-1">
                                            {{ $client->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-primary rounded-3" title="{{ _trans('common.View Profile') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('client.edit')
                                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-secondary rounded-3" title="{{ _trans('common.Edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('client.delete')
                                                <form method="POST" action="{{ route('clients.destroy', $client) }}" class="d-inline" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this client?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="{{ _trans('common.Delete') }}">
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
                                        <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                        {{ _trans('common.No clients found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Pagination -->
    @if ($clients->hasPages())
        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    @endif

    <!-- Create Client Modal -->
    @can('client.create')
        <div class="modal fade" id="createClientModal" tabindex="-1" aria-labelledby="createClientModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold text-dark" id="createClientModalLabel">
                                <i class="bi bi-person-plus-fill text-primary me-2"></i>{{ _trans('common.Add New Client') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-3">
                            <div class="row g-3">
                                <!-- Company Name -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Company Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" placeholder="e.g. Acme Corporation" value="{{ old('company_name') }}" required>
                                    @error('company_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Primary Contact Person -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Primary Contact Person') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_name" class="form-control @error('contact_name') is-invalid @enderror" placeholder="e.g. John Doe" value="{{ old('contact_name') }}" required>
                                    @error('contact_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Email Address') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="e.g. client@company.com" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Phone Number') }}</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="e.g. +1 555-0199" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Website -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Website URL') }}</label>
                                    <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" placeholder="https://example.com" value="{{ old('website') }}">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Industry -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Industry') }}</label>
                                    <input type="text" name="industry" class="form-control @error('industry') is-invalid @enderror" placeholder="e.g. Technology, Retail, Finance" value="{{ old('industry') }}">
                                    @error('industry')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Currency & Status -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Currency') }}</label>
                                    <select name="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
                                        <option value="">-- {{ _trans('common.Select Currency') }} --</option>
                                        @foreach ($currencies as $curr)
                                            <option value="{{ $curr->id }}" {{ old('currency_id') == $curr->id ? 'selected' : '' }}>
                                                {{ $curr->code }} ({{ $curr->symbol }}) — {{ $curr->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('currency_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Status') }} <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach ($statuses as $st)
                                            <option value="{{ $st->value }}" {{ old('status', 'active') === $st->value ? 'selected' : '' }}>
                                                {{ $st->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Country, State, City Cascading -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Country') }}</label>
                                    <select name="country_id" id="modalCountrySelect" class="form-select">
                                        <option value="">-- {{ _trans('common.Select Country') }} --</option>
                                        @foreach ($countries as $c)
                                            <option value="{{ $c->id }}" {{ old('country_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.State / Province') }}</label>
                                    <select name="state_id" id="modalStateSelect" class="form-select">
                                        <option value="">-- {{ _trans('common.Select State') }} --</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.City') }}</label>
                                    <select name="city_id" id="modalCitySelect" class="form-select">
                                        <option value="">-- {{ _trans('common.Select City') }} --</option>
                                    </select>
                                </div>

                                <!-- Address -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Street Address') }}</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="{{ _trans('common.Street address, suite, postal code...') }}">{{ old('address') }}</textarea>
                                </div>

                                <!-- Logo Upload -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Company Logo / Avatar') }}</label>
                                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                                    <div class="form-text small">{{ _trans('common.Recommended square image PNG, JPG, WEBP (Max 2MB)') }}</div>
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Notes -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small mb-1">{{ _trans('common.Notes / Description') }}</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="{{ _trans('common.Internal notes or remarks...') }}">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                                <i class="bi bi-check-lg me-1"></i>{{ _trans('common.Save Client') }}
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
    $(document).ready(function() {
        $('#modalCountrySelect').on('change', function() {
            const countryId = $(this).val();
            const stateSelect = $('#modalStateSelect');
            const citySelect = $('#modalCitySelect');

            stateSelect.html('<option value="">-- {{ _trans('common.Select State') }} --</option>');
            citySelect.html('<option value="">-- {{ _trans('common.Select City') }} --</option>');

            if (countryId) {
                $.get('/states/' + countryId, function(data) {
                    $.each(data, function(index, state) {
                        stateSelect.append('<option value="' + state.id + '">' + state.name + '</option>');
                    });
                });
            }
        });

        $('#modalStateSelect').on('change', function() {
            const stateId = $(this).val();
            const citySelect = $('#modalCitySelect');

            citySelect.html('<option value="">-- {{ _trans('common.Select City') }} --</option>');

            if (stateId) {
                $.get('/cities/' + stateId, function(data) {
                    $.each(data, function(index, city) {
                        citySelect.append('<option value="' + city.id + '">' + city.name + '</option>');
                    });
                });
            }
        });
    });
</script>
@endpush
