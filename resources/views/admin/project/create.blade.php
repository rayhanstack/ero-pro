@extends('admin.layouts.app')
@section('title', _trans('common.Create Project'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Create New Project') }}"
        subtitle="{{ _trans('common.Set up project budget, timeline, deliverables, and team assignments') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Projects'), 'url' => route('projects.index')],
            ['label' => _trans('common.Create Project')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Projects') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('projects.store') }}" class="needs-validation">
        @csrf

        <div class="row g-4">
            {{-- Left Column: General & Scope --}}
            <div class="col-lg-8">
                <x-ui.card :title="_trans('common.Project Information')" icon="bi-folder-plus">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label fw-semibold">
                                {{ _trans('common.Project Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="{{ _trans('common.e.g. NextGen ERP Platform') }}"
                                value="{{ old('name') }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="client_id" class="form-label fw-semibold">
                                {{ _trans('common.Client') }}
                            </label>
                            <select name="client_id" id="client_id" class="form-select select2-element @error('client_id') is-invalid @enderror" data-placeholder="{{ _trans('common.Select Client') }}">
                                <option value="">{{ _trans('common.Select Client (Optional)') }}</option>
                                @foreach ($clients as $c)
                                    <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->company_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">
                                {{ _trans('common.Description & Objectives') }}
                            </label>
                            <textarea name="description"
                                id="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="4"
                                placeholder="{{ _trans('common.Detail the project architecture, goals, tech stack, and key requirements...') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>

                {{-- Team & Squad Assignments --}}
                <div class="mt-4">
                    <x-ui.card :title="_trans('common.Team & Workforce Assignment')" icon="bi-people">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="manager_id" class="form-label fw-semibold">
                                    {{ _trans('common.Project Manager / Lead') }}
                                </label>
                                <select name="manager_id" id="manager_id" class="form-select select2-element @error('manager_id') is-invalid @enderror" data-placeholder="{{ _trans('common.Select Project Manager') }}">
                                    <option value="">{{ _trans('common.Select Project Manager') }}</option>
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ old('manager_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('manager_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="team_ids" class="form-label fw-semibold">
                                    {{ _trans('common.Assign Entire Squads / Teams') }}
                                </label>
                                <select name="team_ids[]" id="team_ids" class="form-select select2-element @error('team_ids') is-invalid @enderror" multiple data-placeholder="{{ _trans('common.Select Teams...') }}">
                                    @foreach ($teams as $t)
                                        <option value="{{ $t->id }}" {{ in_array($t->id, (array) old('team_ids', [])) ? 'selected' : '' }}>
                                            {{ $t->name }} ({{ $t->members->count() }} {{ _trans('common.members') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text small text-muted">{{ _trans('common.All members in selected teams will be automatically assigned.') }}</div>
                                @error('team_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="member_ids" class="form-label fw-semibold">
                                    {{ _trans('common.Individual Members') }}
                                </label>
                                <select name="member_ids[]" id="member_ids" class="form-select select2-element @error('member_ids') is-invalid @enderror" multiple data-placeholder="{{ _trans('common.Select individual members...') }}">
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ in_array($emp->id, (array) old('member_ids', [])) ? 'selected' : '' }}>
                                            {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('member_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>

            {{-- Right Column: Timeline, Budget & Status --}}
            <div class="col-lg-4">
                {{-- Status & Priority --}}
                <x-ui.card :title="_trans('common.Status & Priority')" icon="bi-sliders">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="status" class="form-label fw-semibold">
                                {{ _trans('common.Status') }} <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $st)
                                    <option value="{{ $st->value }}" {{ old('status', 'planning') === $st->value ? 'selected' : '' }}>
                                        {{ $st->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="priority" class="form-label fw-semibold">
                                {{ _trans('common.Priority') }} <span class="text-danger">*</span>
                            </label>
                            <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                @foreach ($priorities as $pr)
                                    <option value="{{ $pr->value }}" {{ old('priority', 'medium') === $pr->value ? 'selected' : '' }}>
                                        {{ $pr->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="progress" class="form-label fw-semibold">
                                {{ _trans('common.Initial Progress (%)') }}
                            </label>
                            <input type="number" min="0" max="100" name="progress" id="progress" class="form-control @error('progress') is-invalid @enderror" value="{{ old('progress', 0) }}">
                            @error('progress')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>

                {{-- Timeline & Budget --}}
                <div class="mt-4">
                    <x-ui.card :title="_trans('common.Timeline & Budget')" icon="bi-calendar-event">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="start_date" class="form-label fw-semibold">{{ _trans('common.Start Date') }}</label>
                                <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="deadline" class="form-label fw-semibold">{{ _trans('common.Deadline') }}</label>
                                <input type="date" name="deadline" id="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline') }}">
                                @error('deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="budget" class="form-label fw-semibold">{{ _trans('common.Budget Amount') }}</label>
                                <input type="number" step="0.01" min="0" name="budget" id="budget" class="form-control @error('budget') is-invalid @enderror" placeholder="0.00" value="{{ old('budget') }}">
                                @error('budget')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="currency_id" class="form-label fw-semibold">{{ _trans('common.Currency') }}</label>
                                <select name="currency_id" id="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
                                    <option value="">{{ _trans('common.Default Currency') }}</option>
                                    @foreach ($currencies as $curr)
                                        <option value="{{ $curr->id }}" {{ old('currency_id') == $curr->id ? 'selected' : '' }}>
                                            {{ $curr->code }} - {{ $curr->name }} ({{ $curr->symbol }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('currency_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-4">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-check2-circle fs-5"></i>
                            <span>{{ _trans('common.Create Project') }}</span>
                        </button>
                        <a href="{{ route('projects.index') }}" class="btn btn-light">
                            {{ _trans('common.Cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2-element').each(function() {
        var $el = $(this);
        $el.select2({
            placeholder: $el.data('placeholder') || 'Select option',
            allowClear: true,
            width: '100%'
        });
    });
});
</script>
@endpush
