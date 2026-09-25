@extends('admin.layouts.app')
@section('title', _trans('common.Language Management'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Language Management') }}"
        subtitle="{{ _trans('common.Manage system languages, RTL text direction, and default language') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Settings'), 'url' => route('settings')],
            ['label' => _trans('common.Languages')],
        ]"
    >
        <x-slot:actions>
            @can('setting.edit')
                <a href="{{ route('languages.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.Add Language') }}</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card :title="_trans('common.Configured Languages')" icon="bi-translate">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">{{ _trans('common.Language') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Code') }}</th>
                        <th class="py-3 px-4">{{ _trans('common.Native Name') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.RTL') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Default') }}</th>
                        <th class="py-3 px-4 text-center">{{ _trans('common.Status') }}</th>
                        <th class="py-3 px-4 text-end">{{ _trans('common.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($languages as $lang)
                        <tr>
                            <td class="py-3 px-4 text-muted">{{ $loop->iteration }}</td>
                            <td class="py-3 px-4 fw-semibold text-dark">
                                {{ $lang->name }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="badge bg-light text-dark border font-monospace">{{ $lang->code }}</span>
                            </td>
                            <td class="py-3 px-4 text-muted">
                                {{ $lang->native ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($lang->rtl)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill">RTL</span>
                                @else
                                    <span class="text-muted small">LTR</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($lang->is_default)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        <i class="bi bi-star-fill me-1"></i>
                                        {{ _trans('common.Default') }}
                                    </span>
                                @else
                                    @can('setting.edit')
                                        <form method="POST" action="{{ route('languages.default', $lang) }}" class="d-inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-xs btn-outline-secondary py-1 px-2 text-xs" title="{{ _trans('common.Set as Default') }}">
                                                {{ _trans('common.Set Default') }}
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($lang->status === 'active')
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">{{ _trans('common.Active') }}</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">{{ _trans('common.Inactive') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @can('setting.edit')
                                        <a href="{{ route('languages.edit', $lang) }}" class="btn btn-sm btn-outline-primary" title="{{ _trans('common.Edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                    @if (!$lang->is_default && $lang->code !== 'en')
                                        @can('setting.delete')
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmDeleteModal"
                                                data-action="{{ route('languages.destroy', $lang) }}"
                                                data-item-name="{{ $lang->name }}"
                                                title="{{ _trans('common.Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">{{ _trans('common.No languages found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($languages->hasPages())
            <div class="p-3 border-top">
                <x-ui.pagination :paginator="$languages" />
            </div>
        @endif
    </x-ui.card>

    <x-ui.confirm-delete />
@endsection
