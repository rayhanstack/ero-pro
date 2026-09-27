@extends('admin.layouts.app')
@section('title', $meeting->title)

@section('content')
    {{-- Meeting Header --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge {{ $meeting->status->badgeClass() }} px-2.5 py-1.5 rounded-pill fs-8">
                            {{ $meeting->status->label() }}
                        </span>
                        <span class="badge {{ $meeting->type->badgeClass() }} px-2.5 py-1.5 rounded-pill fs-8">
                            <i class="bi {{ $meeting->type->icon() }} me-1"></i>{{ $meeting->type->label() }}
                        </span>
                        @if ($meeting->project)
                            <a href="{{ route('projects.show', $meeting->project) }}" class="badge bg-light text-dark border text-decoration-none px-2.5 py-1.5 rounded-pill fs-8">
                                <i class="bi bi-folder2 text-primary me-1"></i>{{ $meeting->project->name }}
                            </a>
                        @endif
                    </div>

                    <h3 class="fw-bold text-dark mb-1">{{ $meeting->title }}</h3>

                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mt-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-calendar3 text-primary"></i>
                            <span class="fw-semibold text-dark">{{ $meeting->date ? $meeting->date->format('l, F d, Y') : _trans('common.N/A') }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-clock text-primary"></i>
                            <span>{{ $meeting->formatted_time_range }} ({{ $meeting->duration_minutes }} {{ _trans('common.mins') }})</span>
                        </div>
                        @if ($meeting->location)
                            <div class="d-flex align-items-center gap-1.5">
                                <i class="bi bi-geo-alt text-danger"></i>
                                <span>{{ $meeting->location }}</span>
                            </div>
                        @endif
                        @if ($meeting->meeting_link)
                            <div class="d-flex align-items-center gap-1.5">
                                <i class="bi bi-camera-video text-info"></i>
                                <a href="{{ $meeting->meeting_link }}" target="_blank" class="text-primary fw-semibold text-decoration-none">
                                    {{ _trans('common.Join Meeting') }} <i class="bi bi-box-arrow-up-right extra-small"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @can('meeting.edit')
                        <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-pencil"></i>
                            <span>{{ _trans('common.Edit') }}</span>
                        </a>
                    @endcan
                    @can('meeting.delete')
                        <form method="POST" action="{{ route('meetings.destroy', $meeting) }}" onsubmit="return confirm('{{ _trans('common.Are you sure you want to cancel/delete this meeting?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                                <i class="bi bi-trash"></i>
                                <span>{{ _trans('common.Cancel Meeting') }}</span>
                            </button>
                        </form>
                    @endcan
                    <a href="{{ route('meetings.index') }}" class="btn btn-sm btn-light border">
                        <i class="bi bi-arrow-left me-1"></i>{{ _trans('common.Back') }}
                    </a>
                </div>
            </div>

            {{-- RSVP Quick Action for Attendee --}}
            @if ($currentUserRsvp && $meeting->status === \App\Enums\MeetingStatusEnum::SCHEDULED)
                <div class="mt-4 p-3 bg-light rounded-3 border d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-check text-primary fs-4"></i>
                        <div>
                            <div class="fw-semibold text-dark">{{ _trans('common.Your RSVP Response') }}</div>
                            <div class="text-muted extra-small">
                                {{ _trans('common.Current status') }}:
                                <span class="badge {{ $currentUserRsvp->response->badgeClass() }} py-0.5 px-2" id="currentRsvpBadge">
                                    {{ $currentUserRsvp->response->label() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('meetings.rsvp', $meeting) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="response" value="accepted">
                            <button type="submit" class="btn btn-sm {{ $currentUserRsvp->response === \App\Enums\MeetingAttendeeResponseEnum::ACCEPTED ? 'btn-success' : 'btn-outline-success' }} d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>{{ _trans('common.Accept') }}</span>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('meetings.rsvp', $meeting) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="response" value="declined">
                            <button type="submit" class="btn btn-sm {{ $currentUserRsvp->response === \App\Enums\MeetingAttendeeResponseEnum::DECLINED ? 'btn-danger' : 'btn-outline-danger' }} d-inline-flex align-items-center gap-1">
                                <i class="bi bi-x-circle-fill"></i>
                                <span>{{ _trans('common.Decline') }}</span>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('meetings.rsvp', $meeting) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="response" value="pending">
                            <button type="submit" class="btn btn-sm {{ $currentUserRsvp->response === \App\Enums\MeetingAttendeeResponseEnum::PENDING ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }} d-inline-flex align-items-center gap-1">
                                <i class="bi bi-hourglass-split"></i>
                                <span>{{ _trans('common.Tentative') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav custom-pills mb-4" id="meetingDetailTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-inline-flex align-items-center gap-2" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overviewTabPane" type="button" role="tab">
                <i class="bi bi-card-text"></i>
                <span>{{ _trans('common.Overview & Agenda') }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="attendees-tab" data-bs-toggle="tab" data-bs-target="#attendeesTabPane" type="button" role="tab">
                <i class="bi bi-people"></i>
                <span>{{ _trans('common.Attendees & RSVP') }}</span>
                <span class="badge bg-white text-dark rounded-pill">{{ $meeting->attendees->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="minutes-tab" data-bs-toggle="tab" data-bs-target="#minutesTabPane" type="button" role="tab">
                <i class="bi bi-journal-text"></i>
                <span>{{ _trans('common.Minutes of Meeting (MoM)') }}</span>
                @if ($meeting->minutes)
                    <span class="badge bg-success text-white rounded-pill"><i class="bi bi-check"></i></span>
                @endif
            </button>
        </li>
    </ul>

    {{-- Tab Contents --}}
    <div class="tab-content" id="meetingDetailTabContent">
        {{-- Tab 1: Overview & Agenda --}}
        <div class="tab-pane fade show active" id="overviewTabPane" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Meeting Agenda & Objective') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            @if ($meeting->agenda)
                                <div class="text-dark line-height-lg" style="white-space: pre-line;">{{ $meeting->agenda }}</div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-card-text fs-2"></i>
                                    <p class="mt-2 small">{{ _trans('common.No detailed agenda recorded for this meeting.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="fw-bold text-dark mb-0">{{ _trans('common.Meeting Host') }}</h6>
                        </div>
                        <div class="card-body p-3">
                            @if ($meeting->organizer)
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $meeting->organizer->avatar_url }}" alt="{{ $meeting->organizer->name }}" class="rounded-circle object-fit-cover shadow-sm" width="48" height="48">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $meeting->organizer->name }}</div>
                                        <div class="small text-muted">{{ $meeting->organizer->employeeDetail?->designation?->name ?? _trans('common.Organizer') }}</div>
                                        <div class="extra-small text-muted" style="font-size: 11px;">{{ $meeting->organizer->email }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted small">{{ _trans('common.Organizer info unavailable') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 bg-white">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="fw-bold text-dark mb-0">{{ _trans('common.Summary Breakdown') }}</h6>
                        </div>
                        <div class="card-body p-3">
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">{{ _trans('common.Total Invited') }}:</span>
                                    <span class="fw-bold text-dark">{{ $meeting->attendees->count() }}</span>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">{{ _trans('common.Accepted') }}:</span>
                                    <span class="fw-bold text-success">{{ $meeting->accepted_count }}</span>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">{{ _trans('common.Awaiting Response') }}:</span>
                                    <span class="fw-bold text-warning">{{ $meeting->pending_count }}</span>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">{{ _trans('common.Declined') }}:</span>
                                    <span class="fw-bold text-danger">{{ $meeting->declined_count }}</span>
                                </li>
                                <li class="d-flex justify-content-between pt-2 border-top">
                                    <span class="text-muted">{{ _trans('common.Minutes Recorded') }}:</span>
                                    <span class="fw-bold text-dark">{{ $meeting->minutes ? _trans('common.Yes') : _trans('common.No') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 2: Attendees & RSVP --}}
        <div class="tab-pane fade" id="attendeesTabPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">{{ _trans('common.Invited Attendees & RSVPs') }}</h5>
                        <p class="text-muted small mb-0">{{ _trans('common.Tracking response confirmation and availability for this meeting') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1">
                            <i class="bi bi-check-lg me-1"></i>{{ $meeting->accepted_count }} {{ _trans('common.Accepted') }}
                        </span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1">
                            <i class="bi bi-hourglass-split me-1"></i>{{ $meeting->pending_count }} {{ _trans('common.Pending') }}
                        </span>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1">
                            <i class="bi bi-x-lg me-1"></i>{{ $meeting->declined_count }} {{ _trans('common.Declined') }}
                        </span>
                    </div>
                </div>

                @if ($meeting->attendees->isEmpty())
                    <div class="card-body text-center py-5">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <h6 class="fw-semibold text-dark mt-3">{{ _trans('common.No attendees added') }}</h6>
                        <p class="text-muted small mb-3">{{ _trans('common.Edit the meeting to invite colleagues and clients.') }}</p>
                        @can('meeting.edit')
                            <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-person-plus me-1"></i>{{ _trans('common.Add Attendees') }}
                            </a>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ _trans('common.Attendee') }}</th>
                                    <th>{{ _trans('common.Role / Affiliation') }}</th>
                                    <th>{{ _trans('common.Type') }}</th>
                                    <th>{{ _trans('common.RSVP Status') }}</th>
                                    <th>{{ _trans('common.Notes / Remarks') }}</th>
                                    <th class="text-end pe-4">{{ _trans('common.Invited At') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($meeting->attendees as $att)
                                    @php
                                        $model = $att->attendee;
                                        $isEmployee = $att->attendee_type === \App\Models\User::class;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            @if ($model)
                                                <div class="d-flex align-items-center gap-2.5">
                                                    @if ($isEmployee)
                                                        <img src="{{ $model->avatar_url }}" alt="{{ $model->name }}" class="rounded-circle object-fit-cover shadow-sm" width="34" height="34">
                                                        <div>
                                                            <div class="fw-bold text-dark">{{ $model->name }}</div>
                                                            <span class="text-muted extra-small" style="font-size: 11px;">{{ $model->email }}</span>
                                                        </div>
                                                    @else
                                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;">
                                                            <i class="bi bi-briefcase"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark">{{ $model->company_name }}</div>
                                                            <span class="text-muted extra-small" style="font-size: 11px;">{{ $model->contact_name }} ({{ $model->email }})</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted small fst-italic">{{ _trans('common.Unknown Attendee') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($isEmployee && $model)
                                                <span class="small text-dark">{{ $model->employeeDetail?->designation?->name ?? _trans('common.Staff') }}</span>
                                            @elseif (!$isEmployee && $model)
                                                <span class="badge bg-light text-dark border">{{ _trans('common.External Client') }}</span>
                                            @else
                                                <span class="small text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $isEmployee ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' : 'bg-info bg-opacity-10 text-info border border-info border-opacity-25' }} px-2 py-1">
                                                {{ $isEmployee ? _trans('common.Internal') : _trans('common.Client') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $att->response->badgeClass() }} px-2 py-1">
                                                <i class="bi {{ $att->response->icon() }} me-1"></i>{{ $att->response->label() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small text-muted">{{ $att->notes ?: '—' }}</span>
                                        </td>
                                        <td class="text-end pe-4 small text-muted">
                                            {{ $att->created_at->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tab 3: Minutes of Meeting (MoM) --}}
        <div class="tab-pane fade" id="minutesTabPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 mb-4 border-bottom">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">
                            <i class="bi bi-journal-text text-primary me-2"></i>{{ _trans('common.Minutes of Meeting (MoM)') }}
                        </h4>
                        <p class="text-muted small mb-0">{{ _trans('common.Document key discussion topics, formal decisions, and action item assignments') }}</p>
                    </div>

                    @if ($meeting->minutes)
                        <div class="text-muted small">
                            <i class="bi bi-person-badge text-primary me-1"></i>
                            {{ _trans('common.Recorded by') }}: <strong class="text-dark">{{ $meeting->minutes->recorder?->name ?? _trans('common.Host') }}</strong>
                            <span class="ms-2">({{ $meeting->minutes->updated_at->format('M d, Y h:i A') }})</span>
                        </div>
                    @endif
                </div>

                @can('meeting.edit')
                    <form method="POST" action="{{ route('meetings.minutes.store', $meeting) }}">
                        @csrf
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark">{{ _trans('common.Discussion Summary & Notes') }} <span class="text-danger">*</span></label>
                                <textarea name="discussion" class="form-control" rows="6" required placeholder="{{ _trans('common.Summarize the discussions held, arguments raised, and general flow of the session...') }}">{{ old('discussion', $meeting->minutes?->discussion) }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark">{{ _trans('common.Decisions Made') }}</label>
                                <textarea name="decisions" class="form-control" rows="3" placeholder="{{ _trans('common.Bullet points of finalized decisions, approvals, or agreed outcomes...') }}">{{ old('decisions', $meeting->minutes?->decisions) }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold text-dark mb-0">{{ _trans('common.Action Items & Deliverables') }}</label>
                                    <button type="button" class="btn btn-xs btn-outline-primary" id="addActionItemBtn">
                                        <i class="bi bi-plus-lg me-1"></i>{{ _trans('common.Add Action Item') }}
                                    </button>
                                </div>

                                <div id="actionItemsContainer" class="d-flex flex-column gap-2">
                                    @php
                                        $actionItems = old('action_items', $meeting->minutes?->action_items ?? []);
                                    @endphp

                                    @forelse ($actionItems as $idx => $item)
                                        <div class="row g-2 align-items-center action-item-row p-2 bg-light rounded-3 border">
                                            <div class="col-md-5">
                                                <input type="text" name="action_items[{{ $idx }}][task]" class="form-control form-control-sm" placeholder="{{ _trans('common.Task description...') }}" value="{{ $item['task'] ?? '' }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="action_items[{{ $idx }}][assignee]" class="form-control form-control-sm" placeholder="{{ _trans('common.Assignee name...') }}" value="{{ $item['assignee'] ?? '' }}">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="date" name="action_items[{{ $idx }}][due_date]" class="form-control form-control-sm" value="{{ $item['due_date'] ?? '' }}">
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <button type="button" class="btn btn-sm btn-light text-danger remove-action-item-btn">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="row g-2 align-items-center action-item-row p-2 bg-light rounded-3 border">
                                            <div class="col-md-5">
                                                <input type="text" name="action_items[0][task]" class="form-control form-control-sm" placeholder="{{ _trans('common.Task description...') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="action_items[0][assignee]" class="form-control form-control-sm" placeholder="{{ _trans('common.Assignee...') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="date" name="action_items[0][due_date]" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <button type="button" class="btn btn-sm btn-light text-danger remove-action-item-btn">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            @if ($meeting->status !== \App\Enums\MeetingStatusEnum::COMPLETED)
                                <div class="col-12">
                                    <div class="form-check form-switch p-3 bg-light rounded-3 border">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="mark_completed" name="mark_completed" value="1" checked>
                                        <label class="form-check-label fw-semibold text-dark" for="mark_completed">
                                            {{ _trans('common.Mark meeting status as "Completed" upon saving minutes') }}
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12 text-end pt-3 border-top">
                                <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-check-circle"></i>
                                    <span>{{ _trans('common.Save Minutes of Meeting') }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    @if ($meeting->minutes)
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark">{{ _trans('common.Discussion Summary') }}</h6>
                            <div class="text-dark bg-light p-3 rounded-3 border" style="white-space: pre-line;">{{ $meeting->minutes->discussion }}</div>
                        </div>

                        @if ($meeting->minutes->decisions)
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark">{{ _trans('common.Decisions Made') }}</h6>
                                <div class="text-dark bg-light p-3 rounded-3 border" style="white-space: pre-line;">{{ $meeting->minutes->decisions }}</div>
                            </div>
                        @endif

                        @if (!empty($meeting->minutes->action_items))
                            <div>
                                <h6 class="fw-bold text-dark mb-2">{{ _trans('common.Action Items') }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ _trans('common.Action Item') }}</th>
                                                <th>{{ _trans('common.Assignee') }}</th>
                                                <th>{{ _trans('common.Target Date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($meeting->minutes->action_items as $item)
                                                <tr>
                                                    <td class="fw-semibold">{{ $item['task'] ?? '—' }}</td>
                                                    <td>{{ $item['assignee'] ?? '—' }}</td>
                                                    <td>{{ !empty($item['due_date']) ? \Carbon\Carbon::parse($item['due_date'])->format('M d, Y') : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-1"></i>
                            <h6 class="mt-2">{{ _trans('common.No minutes recorded yet.') }}</h6>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
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
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.375rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var actionItemIdx = {{ count($actionItems ?? [0]) }};

    $('#addActionItemBtn').on('click', function() {
        actionItemIdx++;
        var rowHtml = `
            <div class="row g-2 align-items-center action-item-row p-2 bg-light rounded-3 border">
                <div class="col-md-5">
                    <input type="text" name="action_items[${actionItemIdx}][task]" class="form-control form-control-sm" placeholder="{{ _trans('common.Task description...') }}" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="action_items[${actionItemIdx}][assignee]" class="form-control form-control-sm" placeholder="{{ _trans('common.Assignee...') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="action_items[${actionItemIdx}][due_date]" class="form-control form-control-sm">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-light text-danger remove-action-item-btn">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#actionItemsContainer').append(rowHtml);
    });

    $(document).on('click', '.remove-action-item-btn', function() {
        if ($('.action-item-row').length > 1) {
            $(this).closest('.action-item-row').remove();
        } else {
            $(this).closest('.action-item-row').find('input').val('');
        }
    });
});
</script>
@endpush
