@extends('admin.layouts.app')
@section('title', _trans('common.Edit Team') . ' - ' . $team->name)

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Edit Team') }}"
        subtitle="{{ $team->name }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Teams'), 'url' => route('teams.index')],
            ['label' => $team->name, 'url' => route('teams.show', $team)],
            ['label' => _trans('common.Edit')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Team') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <x-ui.card :title="_trans('common.Edit Team Details')" icon="bi-people-fill">
                <form method="POST" action="{{ route('teams.update', $team) }}" class="needs-validation">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label fw-semibold">
                                {{ _trans('common.Team Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $team->name) }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="status" class="form-label fw-semibold">
                                {{ _trans('common.Status') }} <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $st)
                                    <option value="{{ $st->value }}" {{ old('status', $team->status->value) === $st->value ? 'selected' : '' }}>
                                        {{ $st->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="lead_id" class="form-label fw-semibold">
                                {{ _trans('common.Team Lead') }}
                            </label>
                            <select name="lead_id" id="lead_id" class="form-select select2-element @error('lead_id') is-invalid @enderror" data-placeholder="{{ _trans('common.Select Team Lead') }}">
                                <option value="">{{ _trans('common.Select Team Lead') }}</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('lead_id', $team->lead_id) == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }} · {{ $emp->employeeDetail?->department?->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('lead_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="member_ids" class="form-label fw-semibold">
                                {{ _trans('common.Team Members') }}
                            </label>
                            <select name="member_ids[]" id="member_ids" class="form-select select2-element @error('member_ids') is-invalid @enderror" multiple data-placeholder="{{ _trans('common.Select Team Members (Multiple)') }}">
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ in_array($emp->id, (array) old('member_ids', $selectedMemberIds)) ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }} · {{ $emp->employeeDetail?->department?->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">{{ _trans('common.Hold Ctrl/Cmd or search to select multiple members.') }}</div>
                            @error('member_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="description" class="form-label fw-semibold">
                                {{ _trans('common.Description') }}
                            </label>
                            <textarea name="description"
                                id="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="4">{{ old('description', $team->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('teams.show', $team) }}" class="btn btn-light">{{ _trans('common.Cancel') }}</a>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Update Team') }}</span>
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
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
