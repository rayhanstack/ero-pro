@extends('admin.layouts.app')
@section('title', _trans('common.Weekly Holidays (Weekends)'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Weekly Holidays (Weekends)') }}"
        subtitle="{{ _trans('common.Configure company weekly non-working days for payroll, attendance, and leave tracking') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.HR')],
            ['label' => _trans('common.Weekends')],
        ]"
    />

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <x-ui.card :title="_trans('common.Weekly Non-Working Days Configuration')" icon="bi-calendar2-week">
                <p class="text-muted small mb-4">
                    {{ _trans('common.Toggle the switches below to mark days as weekly holidays (weekends). Changes immediately apply to attendance calculations, payroll working days, and calendar queries.') }}
                </p>

                <form method="POST" action="{{ route('weekends.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="list-group list-group-flush border rounded mb-4">
                        @foreach ($weekends as $weekend)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded p-2 text-center" style="width: 45px;">
                                        <i class="bi bi-calendar-day text-primary fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">{{ $weekend->name }}</h6>
                                        <small class="text-muted">
                                            @if ($weekend->day_of_week === 5)
                                                {{ _trans('common.Standard weekend in Bangladesh & Middle East') }}
                                            @elseif (in_array($weekend->day_of_week, [0, 6]))
                                                {{ _trans('common.Standard weekend internationally (Sat / Sun)') }}
                                            @else
                                                {{ _trans('common.Standard business working day') }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                <div>
                                    <x-form.switch
                                        name="weekends[]"
                                        id="weekend_day_{{ $weekend->day_of_week }}"
                                        :value="$weekend->day_of_week"
                                        :checked="$weekend->is_weekend"
                                    />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @can('weekend.edit')
                        <div class="d-flex justify-content-end gap-2 pt-2">
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i>
                                <span>{{ _trans('common.Save Weekend Settings') }}</span>
                            </button>
                        </div>
                    @endcan
                </form>
            </x-ui.card>
        </div>
    </div>
@endsection
