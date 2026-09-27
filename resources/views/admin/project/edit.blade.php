@extends('admin.layouts.app')
@section('title', _trans('common.Edit Project') . ' - ' . $project->name)

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Edit Project') }}"
        subtitle="{{ $project->name }} ({{ $project->code }})"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Projects'), 'url' => route('projects.index')],
            ['label' => $project->name, 'url' => route('projects.show', $project)],
            ['label' => _trans('common.Edit')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Project') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('projects.update', $project) }}" class="needs-validation">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Left Column: General & Scope --}}
            <div class="col-lg-8">
                <x-ui.card :title="_trans('common.Project Information')" icon="bi-folder-check">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label fw-semibold">
                                {{ _trans('common.Project Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $project->name) }}"
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
                                    <option value="{{ $c->id }}" {{ old('client_id', $project->client_id) == $c->id ? 'selected' : '' }}>
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
                                rows="4">{{ old('description', $project->description) }}</textarea>
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
                                        <option value="{{ $emp->id }}" {{ old('manager_id', $project->manager_id) == $emp->id ? 'selected' : '' }}>
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
                                        <option value="{{ $t->id }}">
                                            {{ $t->name }} ({{ $t->members->count() }} {{ _trans('common.members') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text small text-muted">{{ _trans('common.Adding a squad will bulk-add its members.') }}</div>
                                @error('team_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="member_ids" class="form-label fw-semibold">
                                    {{ _trans('common.Assigned Members') }}
                                </label>
                                <select name="member_ids[]" id="member_ids" class="form-select select2-element @error('member_ids') is-invalid @enderror" multiple data-placeholder="{{ _trans('common.Select individual members...') }}">
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ in_array($emp->id, (array) old('member_ids', $selectedMemberIds)) ? 'selected' : '' }}>
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
                                    <option value="{{ $st->value }}" {{ old('status', $project->status->value) === $st->value ? 'selected' : '' }}>
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
                                    <option value="{{ $pr->value }}" {{ old('priority', $project->priority->value) === $pr->value ? 'selected' : '' }}>
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
                                {{ _trans('common.Progress (%)') }}
                            </label>
                            <input type="number" min="0" max="100" name="progress" id="progress" class="form-control @error('progress') is-invalid @enderror" value="{{ old('progress', $project->progress) }}">
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
                                <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="deadline" class="form-label fw-semibold">{{ _trans('common.Deadline') }}</label>
                                <input type="date" name="deadline" id="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline', $project->deadline?->format('Y-m-d')) }}">
                                @error('deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="budget" class="form-label fw-semibold">{{ _trans('common.Budget Amount') }}</label>
                                <input type="number" step="0.01" min="0" name="budget" id="budget" class="form-control @error('budget') is-invalid @enderror" value="{{ old('budget', $project->budget) }}">
                                @error('budget')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="currency_id" class="form-label fw-semibold">{{ _trans('common.Currency') }}</label>
                                <select name="currency_id" id="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
                                    <option value="">{{ _trans('common.Default Currency') }}</option>
                                    @foreach ($currencies as $curr)
                                        <option value="{{ $curr->id }}" {{ old('currency_id', $project->currency_id) == $curr->id ? 'selected' : '' }}>
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
                            <span>{{ _trans('common.Update Project') }}</span>
                        </button>
                        <a href="{{ route('projects.show', $project) }}" class="btn btn-light">
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
