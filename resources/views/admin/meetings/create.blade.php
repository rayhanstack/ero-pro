@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Schedule New Meeting'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Schedule New Meeting') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Set meeting agenda, book room or virtual link, and invite employees and clients') }}</p>
        </div>
        <a href="{{ route('meetings.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>
            <span>{{ _trans('common.Back to Calendar') }}</span>
        </a>
    </div>

    {{-- Conflict Alert Container --}}
    <div id="conflictAlertBox" class="alert alert-warning border-warning shadow-sm rounded-4 d-none mb-4" role="alert">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-3 mt-1"></i>
            <div class="flex-grow-1">
                <h6 class="alert-heading fw-bold mb-1">{{ _trans('common.Scheduling Conflict Detected') }}</h6>
                <ul id="conflictList" class="mb-2 ps-3 small text-dark"></ul>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="ignore_conflicts_checkbox" name="ignore_conflicts" value="1" form="meetingForm">
                    <label class="form-check-label small fw-semibold text-dark" for="ignore_conflicts_checkbox">
                        {{ _trans('common.Override conflict and schedule anyway') }}
                    </label>
                </div>
            </div>
        </div>
    </div>

    @if(session('conflict_warnings'))
        <div class="alert alert-warning border-warning shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">{{ _trans('common.Scheduling Conflict Detected') }}</h6>
                    <ul class="mb-2 ps-3 small text-dark">
                        @foreach(session('conflict_warnings') as $warn)
                            <li>{{ $warn }}</li>
                        @endforeach
                    </ul>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="session_ignore_conflicts" name="ignore_conflicts" value="1" form="meetingForm">
                        <label class="form-check-label small fw-semibold text-dark" for="session_ignore_conflicts">
                            {{ _trans('common.Check this box to override conflict and force schedule') }}
                        </label>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="{{ route('meetings.store') }}" id="meetingForm" class="needs-validation">
                @csrf

                <div class="row g-4">
                    {{-- Basic Info Section --}}
                    <div class="col-12">
                        <h5 class="fw-bold text-dark mb-1 pb-2 border-bottom">
                            <i class="bi bi-info-circle text-primary me-2"></i>{{ _trans('common.General Details') }}
                        </h5>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Meeting Title') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" required placeholder="{{ _trans('common.e.g. Sprint Planning, Client Requirement Sync') }}" value="{{ old('title') }}">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Associated Project') }}</label>
                        <select name="project_id" class="form-select @error('project_id') is-invalid @enderror">
                            <option value="">{{ _trans('common.No Project (General)') }}</option>
                            @foreach ($projects as $proj)
                                <option value="{{ $proj->id }}" {{ (old('project_id', $defaultProjectId) == $proj->id) ? 'selected' : '' }}>
                                    {{ $proj->name }} ({{ $proj->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Timing Section --}}
                    <div class="col-12">
                        <h5 class="fw-bold text-dark mb-1 pb-2 border-bottom mt-2">
                            <i class="bi bi-clock text-primary me-2"></i>{{ _trans('common.Date & Time Schedule') }}
                        </h5>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="meeting_date" class="form-control conflict-check-trigger @error('date') is-invalid @enderror" required value="{{ old('date', $defaultDate) }}">
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Start Time') }} <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="meeting_start_time" class="form-control conflict-check-trigger @error('start_time') is-invalid @enderror" required value="{{ old('start_time', $defaultStartTime) }}">
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.End Time') }} <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" id="meeting_end_time" class="form-control conflict-check-trigger @error('end_time') is-invalid @enderror" required value="{{ old('end_time', $defaultEndTime) }}">
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Modality & Location --}}
                    <div class="col-12">
                        <h5 class="fw-bold text-dark mb-1 pb-2 border-bottom mt-2">
                            <i class="bi bi-geo-alt text-primary me-2"></i>{{ _trans('common.Format & Venue') }}
                        </h5>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Meeting Format') }} <span class="text-danger">*</span></label>
                        <select name="type" id="meeting_type" class="form-select @error('type') is-invalid @enderror" required>
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}" {{ old('type', 'in_person') == $t->value ? 'selected' : '' }}>
                                    {{ $t->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Location / Conference Room') }}</label>
                        <input type="text" name="location" id="meeting_location" class="form-control conflict-check-trigger @error('location') is-invalid @enderror" placeholder="{{ _trans('common.e.g. Boardroom A, 2nd Floor Room 204') }}" value="{{ old('location') }}">
                        <div class="form-text extra-small text-muted">{{ _trans('common.Monitored for simultaneous room double-bookings.') }}</div>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Virtual Meeting Link') }}</label>
                        <input type="url" name="meeting_link" class="form-control @error('meeting_link') is-invalid @enderror" placeholder="{{ _trans('common.https://meet.google.com/xyz or Zoom URL') }}" value="{{ old('meeting_link') }}">
                        @error('meeting_link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Organizer & Attendees --}}
                    <div class="col-12">
                        <h5 class="fw-bold text-dark mb-1 pb-2 border-bottom mt-2">
                            <i class="bi bi-people text-primary me-2"></i>{{ _trans('common.Host & Attendees') }}
                        </h5>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Organizer / Host') }} <span class="text-danger">*</span></label>
                        <select name="organizer_id" id="meeting_organizer" class="form-select conflict-check-trigger select2-organizer @error('organizer_id') is-invalid @enderror" required>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('organizer_id', auth()->id()) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Host') }})
                                </option>
                            @endforeach
                        </select>
                        @error('organizer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Employee Attendees') }}</label>
                        <select name="employee_attendees[]" id="employee_attendees" class="form-select select2-attendees @error('employee_attendees') is-invalid @enderror" multiple data-placeholder="{{ _trans('common.Select colleagues...') }}">
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}" {{ (is_array(old('employee_attendees')) && in_array($emp->id, old('employee_attendees'))) ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? _trans('common.Employee') }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_attendees')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Client Attendees') }}</label>
                        <select name="client_attendees[]" id="client_attendees" class="form-select select2-attendees @error('client_attendees') is-invalid @enderror" multiple data-placeholder="{{ _trans('common.Select clients...') }}">
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ (is_array(old('client_attendees')) && in_array($client->id, old('client_attendees'))) ? 'selected' : '' }}>
                                    {{ $client->company_name }} ({{ $client->contact_name }})
                                </option>
                            @endforeach
                        </select>
                        @error('client_attendees')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Agenda / Notes --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">{{ _trans('common.Agenda & Objective') }}</label>
                        <textarea name="agenda" class="form-control @error('agenda') is-invalid @enderror" rows="4" placeholder="{{ _trans('common.Outline the talking points, topics to be discussed, and expected deliverables...') }}">{{ old('agenda') }}</textarea>
                        @error('agenda')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top">
                    <a href="{{ route('meetings.index') }}" class="btn btn-light px-4">{{ _trans('common.Cancel') }}</a>
                    <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-1">
                        <i class="bi bi-calendar-plus"></i>
                        <span>{{ _trans('common.Schedule & Send Invites') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2-attendees').select2({
        placeholder: function() {
            return $(this).data('placeholder');
        },
        allowClear: true,
        width: '100%'
    });

    var conflictTimer = null;

    function checkSchedulingConflicts() {
        var date = $('#meeting_date').val();
        var startTime = $('#meeting_start_time').val();
        var endTime = $('#meeting_end_time').val();
        var location = $('#meeting_location').val();
        var organizerId = $('#meeting_organizer').val();

        if (!date || !startTime || !endTime) {
            $('#conflictAlertBox').addClass('d-none');
            return;
        }

        $.ajax({
            url: "{{ route('meetings.check-conflict') }}",
            type: 'GET',
            data: {
                date: date,
                start_time: startTime,
                end_time: endTime,
                location: location,
                organizer_id: organizerId
            },
            success: function(res) {
                if (res.has_conflict && res.conflicts.length > 0) {
                    var html = '';
                    res.conflicts.forEach(function(c) {
                        html += '<li>' + c + '</li>';
                    });
                    $('#conflictList').html(html);
                    $('#conflictAlertBox').removeClass('d-none');
                } else {
                    $('#conflictAlertBox').addClass('d-none');
                }
            }
        });
    }

    $('.conflict-check-trigger').on('change keyup', function() {
        clearTimeout(conflictTimer);
        conflictTimer = setTimeout(checkSchedulingConflicts, 400);
    });

    // Run initial check if date/time are filled
    checkSchedulingConflicts();
});
</script>
@endpush
