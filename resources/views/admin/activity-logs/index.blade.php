@extends('admin.layouts.app')
@section('title', _trans('common.Activity Logs'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Activity Logs') }}"
        subtitle="{{ _trans('common.Track and audit system changes and user activities') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Activity Logs')],
        ]"
    />

    <x-ui.card class="mb-4">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">{{ _trans('common.User') }}</label>
                <select name="user_id" class="form-select">
                    <option value="">{{ _trans('common.All Users') }}</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-2">
                <label class="form-label small text-muted mb-1">{{ _trans('common.Action') }}</label>
                <select name="action" class="form-select">
                    <option value="">{{ _trans('common.All Actions') }}</option>
                    @foreach ($actionTypes as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                            {{ formatTitleCase($act) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-2">
                <label class="form-label small text-muted mb-1">{{ _trans('common.From Date') }}</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>

            <div class="col-12 col-md-2">
                <label class="form-label small text-muted mb-1">{{ _trans('common.To Date') }}</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            <div class="col-12 col-md-3 d-flex align-items-end gap-2 pt-md-4">
                <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-funnel"></i>
                    <span>{{ _trans('common.Filter') }}</span>
                </button>
                @if (request()->hasAny(['user_id', 'action', 'date_from', 'date_to', 'module']))
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary" title="{{ _trans('common.Reset') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card :title="_trans('common.System Activity Log')" icon="bi-clock-history">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4">{{ _trans('common.Timestamp') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.User') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Action') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Subject') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Changes') }}</th>
                        <th class="py-3 px-4 text-end">{{ _trans('common.IP Address') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="py-3 px-4 text-muted small text-nowrap">
                                <div>{{ formatDateTime($log->created_at) }}</div>
                                <div class="text-xs text-muted">{{ formatDate($log->created_at, 'humanDiff') }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if ($log->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $log->user->avatar_url }}"
                                            alt="{{ $log->user->name }}"
                                            class="rounded-circle object-fit-cover"
                                            width="32"
                                            height="32">
                                        <div>
                                            <div class="fw-semibold text-dark small">{{ $log->user->name }}</div>
                                            <div class="text-muted text-xs">{{ $log->user->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill">
                                        <i class="bi bi-robot me-1"></i>
                                        {{ _trans('common.System') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $badgeClass = match ($log->action) {
                                        'created' => 'bg-success-subtle text-success border border-success-subtle',
                                        'updated', 'profile.updated' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'deleted' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        'login' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                        'password.changed' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2.5 py-1 rounded-pill fw-semibold">
                                    {{ formatTitleCase($log->action) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if ($log->subject_type)
                                    <span class="small fw-semibold text-dark">
                                        {{ class_basename($log->subject_type) }}
                                    </span>
                                    @if ($log->subject_id)
                                        <span class="badge bg-light text-muted border ms-1">#{{ $log->subject_id }}</span>
                                    @endif
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if (!empty($log->new) || !empty($log->old))
                                    <button type="button"
                                        class="btn btn-xs btn-outline-secondary py-1 px-2 text-xs d-inline-flex align-items-center gap-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#changeModal_{{ $log->id }}">
                                        <i class="bi bi-eye"></i>
                                        <span>{{ _trans('common.View Details') }}</span>
                                    </button>

                                    <!-- Details Modal -->
                                    <div class="modal fade" id="changeModal_{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-light">
                                                    <h6 class="modal-title fw-bold">
                                                        <i class="bi bi-clock-history me-1 text-primary"></i>
                                                        {{ _trans('common.Activity Details') }} #{{ $log->id }}
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row g-3">
                                                        @if (!empty($log->old))
                                                            <div class="col-md-6">
                                                                <h6 class="fw-bold small text-muted mb-2">{{ _trans('common.Previous Data') }}</h6>
                                                                <pre class="bg-light p-3 rounded border text-xs mb-0 overflow-auto" style="max-height: 250px;">{{ json_encode($log->old, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                            </div>
                                                        @endif
                                                        @if (!empty($log->new))
                                                            <div class="col-md-{{ empty($log->old) ? '12' : '6' }}">
                                                                <h6 class="fw-bold small text-muted mb-2">{{ _trans('common.New Data') }}</h6>
                                                                <pre class="bg-light p-3 rounded border text-xs mb-0 overflow-auto" style="max-height: 250px;">{{ json_encode($log->new, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ _trans('common.Close') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-end small text-muted font-monospace">
                                {{ $log->ip ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-clock-history fs-1 text-muted d-block mb-2"></i>
                                <span class="text-muted">{{ _trans('common.No activity logs found.') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="p-3 border-top">
                <x-ui.pagination :paginator="$logs" />
            </div>
        @endif
    </x-ui.card>
@endsection
