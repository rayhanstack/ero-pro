@extends('admin.layouts.app')
@section('title', $team->name . ' - ' . _trans('common.Team Profile'))

@section('content')
    <x-ui.page-header
        title="{{ $team->name }}"
        subtitle="{{ _trans('common.Team Profile, Member Management, and Project Assignments') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Teams'), 'url' => route('teams.index')],
            ['label' => $team->name],
        ]"
    >
        <x-slot:actions>
            <div class="d-flex gap-2">
                @can('team.edit')
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addMembersModal">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>{{ _trans('common.Add Members') }}</span>
                    </button>
                    <a href="{{ route('teams.edit', $team) }}" class="btn btn-outline-warning d-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil"></i>
                        <span>{{ _trans('common.Edit Team') }}</span>
                    </a>
                @endcan
                <a href="{{ route('teams.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ _trans('common.Back to Teams') }}</span>
                </a>
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Team Overview Header Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                {{-- Left: Team Title, Badges, Description --}}
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="bi bi-people-fill fs-2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h3 class="fw-bold text-dark mb-0">{{ $team->name }}</h3>
                                <span class="badge {{ $team->status->badgeClass() }} rounded-pill px-3 py-1.5">
                                    {{ $team->status->label() }}
                                </span>
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-calendar3 me-1"></i>{{ _trans('common.Created on') }} {{ $team->created_at->format('F d, Y') }}
                            </div>
                        </div>
                    </div>
                    <p class="text-muted mb-0 mt-3">
                        {{ $team->description ?: _trans('common.No detailed description provided for this team.') }}
                    </p>
                </div>

                {{-- Right: Team Lead Quick Card --}}
                <div class="col-lg-5 border-start-lg ps-lg-4">
                    <div class="bg-light rounded-3 p-3">
                        <div class="text-muted small fw-semibold text-uppercase mb-2" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="bi bi-star-fill text-warning me-1"></i>{{ _trans('common.Team Lead') }}
                        </div>
                        @if ($team->lead)
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $team->lead->avatar_url }}" alt="{{ $team->lead->name }}" class="rounded-circle object-fit-cover shadow-sm" width="48" height="48">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-bold text-dark text-truncate">{{ $team->lead->name }}</div>
                                    <div class="text-muted small text-truncate">
                                        {{ $team->lead->employeeDetail?->designation?->name ?? _trans('common.Lead') }} · {{ $team->lead->employeeDetail?->department?->name ?? '' }}
                                    </div>
                                    <div class="text-muted extra-small text-truncate mt-1" style="font-size: 12px;">
                                        <i class="bi bi-envelope me-1"></i>{{ $team->lead->email }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="d-flex align-items-center gap-2 text-muted fst-italic py-2">
                                <i class="bi bi-person-x fs-4"></i>
                                <span>{{ _trans('common.No Team Lead currently assigned.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills custom-pills mb-4" id="teamTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-flex align-items-center gap-2 px-4 py-2.5" id="members-tab" data-bs-toggle="tab" data-bs-target="#membersTabPane" type="button" role="tab" aria-controls="membersTabPane" aria-selected="true">
                <i class="bi bi-people"></i>
                <span>{{ _trans('common.Team Members') }}</span>
                <span class="badge bg-primary text-white rounded-pill ms-1">{{ $team->members->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2 px-4 py-2.5" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projectsTabPane" type="button" role="tab" aria-controls="projectsTabPane" aria-selected="false">
                <i class="bi bi-folder2-open"></i>
                <span>{{ _trans('common.Projects & Squad Tasks') }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2 px-4 py-2.5" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overviewTabPane" type="button" role="tab" aria-controls="overviewTabPane" aria-selected="false">
                <i class="bi bi-info-circle"></i>
                <span>{{ _trans('common.Team Details') }}</span>
            </button>
        </li>
    </ul>

    {{-- Tabs Content --}}
    <div class="tab-content" id="teamTabsContent">
        {{-- Tab 1: Team Members --}}
        <div class="tab-pane fade show active" id="membersTabPane" role="tabpanel" aria-labelledby="members-tab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Assigned Members') }}</h5>
                        <p class="text-muted small mb-0">{{ _trans('common.Employees currently working within this team') }}</p>
                    </div>
                    @can('team.edit')
                        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addMembersModal">
                            <i class="bi bi-person-plus-fill"></i>
                            <span>{{ _trans('common.Add Members') }}</span>
                        </button>
                    @endcan
                </div>

                @if ($team->members->isEmpty())
                    <div class="card-body text-center py-5">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <h6 class="fw-semibold text-dark mt-3">{{ _trans('common.No members assigned yet') }}</h6>
                        <p class="text-muted small mb-3">{{ _trans('common.Assign employees to this team to start collaborating on projects.') }}</p>
                        @can('team.edit')
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMembersModal">
                                <i class="bi bi-person-plus-fill me-1"></i>{{ _trans('common.Assign Members') }}
                            </button>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ _trans('common.Employee') }}</th>
                                    <th>{{ _trans('common.Code') }}</th>
                                    <th>{{ _trans('common.Designation') }}</th>
                                    <th>{{ _trans('common.Department') }}</th>
                                    <th>{{ _trans('common.Email / Phone') }}</th>
                                    <th>{{ _trans('common.Role in Team') }}</th>
                                    @can('team.edit')
                                        <th class="text-end pe-4">{{ _trans('common.Actions') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($team->members as $member)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="rounded-circle object-fit-cover shadow-sm" width="38" height="38">
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $member->name }}</div>
                                                    <span class="badge bg-light text-muted border extra-small" style="font-size: 11px;">
                                                        {{ $member->status->label() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="font-monospace text-muted small">{{ $member->emp_code }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-medium text-dark">{{ $member->employeeDetail?->designation?->name ?? _trans('common.N/A') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ $member->employeeDetail?->department?->name ?? _trans('common.General') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small text-dark">{{ $member->email }}</div>
                                            @if ($member->phone)
                                                <div class="text-muted extra-small" style="font-size: 11px;">{{ $member->phone }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($team->lead_id === $member->id)
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1">
                                                    <i class="bi bi-star-fill me-1"></i>{{ _trans('common.Team Lead') }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1">
                                                    {{ _trans('common.Member') }}
                                                </span>
                                            @endif
                                        </td>
                                        @can('team.edit')
                                            <td class="text-end pe-4">
                                                <form method="POST" action="{{ route('teams.members.destroy', [$team, $member]) }}" onsubmit="return confirm('{{ _trans('common.Are you sure you want to remove this member from the team?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" title="{{ _trans('common.Remove from Team') }}">
                                                        <i class="bi bi-person-x"></i>
                                                        <span class="d-none d-md-inline">{{ _trans('common.Remove') }}</span>
                                                    </button>
                                                </form>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tab 2: Projects Placeholder --}}
        <div class="tab-pane fade" id="projectsTabPane" role="tabpanel" aria-labelledby="projects-tab">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 80px; height: 80px;">
                            <i class="bi bi-folder2-open fs-1"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-dark">{{ _trans('common.Project Squad Integration') }}</h4>
                    <p class="text-muted mx-auto" style="max-width: 540px;">
                        {{ _trans('common.This team will be linked directly to upcoming client project squads, sprint milestones, and task boards. Project assignments and delivery metrics will be displayed here.') }}
                    </p>

                    <div class="row g-3 justify-content-center mt-3" style="max-width: 600px; margin: 0 auto;">
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <div class="fs-4 fw-bold text-primary">0</div>
                                <div class="text-muted small">{{ _trans('common.Active Projects') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <div class="fs-4 fw-bold text-success">0</div>
                                <div class="text-muted small">{{ _trans('common.Tasks Completed') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <div class="fs-4 fw-bold text-info">100%</div>
                                <div class="text-muted small">{{ _trans('common.Sprint Health') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 3: Team Details / Overview --}}
        <div class="tab-pane fade" id="overviewTabPane" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row g-4">
                <div class="col-lg-8">
                    <x-ui.card :title="_trans('common.Team Scope & Objectives')" icon="bi-card-text">
                        <div class="p-2">
                            <h6 class="fw-bold text-dark mb-2">{{ _trans('common.Description') }}</h6>
                            <p class="text-muted mb-4 lh-base">
                                {{ $team->description ?: _trans('common.No detailed description provided.') }}
                            </p>

                            <h6 class="fw-bold text-dark mb-2">{{ _trans('common.Team Status') }}</h6>
                            <span class="badge {{ $team->status->badgeClass() }} rounded-pill px-3 py-1.5 mb-4">
                                {{ $team->status->label() }}
                            </span>

                            <h6 class="fw-bold text-dark mb-2">{{ _trans('common.Audit & Timestamps') }}</h6>
                            <div class="row g-2 text-muted small">
                                <div class="col-sm-6">
                                    <strong>{{ _trans('common.Created at') }}:</strong> {{ $team->created_at->format('M d, Y h:i A') }}
                                </div>
                                <div class="col-sm-6">
                                    <strong>{{ _trans('common.Last Updated') }}:</strong> {{ $team->updated_at->format('M d, Y h:i A') }}
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <div class="col-lg-4">
                    <x-ui.card :title="_trans('common.Team Leadership')" icon="bi-person-badge">
                        @if ($team->lead)
                            <div class="text-center py-3">
                                <img src="{{ $team->lead->avatar_url }}" alt="{{ $team->lead->name }}" class="rounded-circle object-fit-cover shadow-sm mb-3" width="80" height="80">
                                <h6 class="fw-bold text-dark mb-1">{{ $team->lead->name }}</h6>
                                <span class="badge bg-warning bg-opacity-10 text-warning mb-3">
                                    <i class="bi bi-star-fill me-1"></i>{{ _trans('common.Team Lead') }}
                                </span>
                                <div class="text-muted small text-start border-top pt-3 mt-2">
                                    <div class="mb-1"><i class="bi bi-briefcase me-2"></i>{{ $team->lead->employeeDetail?->designation?->name ?? _trans('common.Lead') }}</div>
                                    <div class="mb-1"><i class="bi bi-building me-2"></i>{{ $team->lead->employeeDetail?->department?->name ?? _trans('common.General') }}</div>
                                    <div class="mb-1"><i class="bi bi-envelope me-2"></i>{{ $team->lead->email }}</div>
                                    @if ($team->lead->phone)
                                        <div><i class="bi bi-telephone me-2"></i>{{ $team->lead->phone }}</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted fst-italic">
                                <i class="bi bi-person-x fs-2"></i>
                                <div class="mt-2">{{ _trans('common.No lead assigned') }}</div>
                            </div>
                        @endif
                    </x-ui.card>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Members Modal --}}
    @can('team.edit')
        <div class="modal fade" id="addMembersModal" tabindex="-1" aria-labelledby="addMembersModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="{{ route('teams.members.store', $team) }}" class="needs-validation">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold" id="addMembersModalLabel">
                                <i class="bi bi-person-plus-fill text-primary me-2"></i>{{ _trans('common.Add Members to Team') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            @if ($availableEmployees->isEmpty())
                                <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                                    <i class="bi bi-info-circle-fill fs-5"></i>
                                    <div>{{ _trans('common.All active employees are already assigned to this team.') }}</div>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label for="modal_member_ids" class="form-label fw-semibold">
                                        {{ _trans('common.Select Employees') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="member_ids[]" id="modal_member_ids" class="form-select select2-element" multiple required data-placeholder="{{ _trans('common.Select employees to add...') }}">
                                        @foreach ($availableEmployees as $emp)
                                            <option value="{{ $emp->id }}">
                                                {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }} · {{ $emp->employeeDetail?->department?->name ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small text-muted mt-2">
                                        {{ _trans('common.Select one or more employees to add to this team.') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                            @if (!$availableEmployees->isEmpty())
                                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-check-lg"></i>
                                    <span>{{ _trans('common.Add Members') }}</span>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('styles')
<style>
.custom-pills .nav-link {
    color: #4b5563;
    font-weight: 500;
    border-radius: 0.5rem;
    background-color: #f3f4f6;
    margin-right: 0.5rem;
    transition: all 0.2s ease;
}
.custom-pills .nav-link.active {
    color: #ffffff;
    background-color: #4f46e5;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
}
@media (min-width: 992px) {
    .border-start-lg {
        border-left: 1px solid #e5e7eb !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#addMembersModal').on('shown.bs.modal', function () {
        $(this).find('.select2-element').each(function() {
            var $el = $(this);
            $el.select2({
                placeholder: $el.data('placeholder') || 'Select option',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#addMembersModal')
            });
        });
    });
});
</script>
@endpush
