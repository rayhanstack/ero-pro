@props([
    'headers' => [],
    'empty' => false,
    'emptyMessage' => 'No data available at the moment',
    'emptySubtitle' => 'Try adjusting your search or filters to find what you are looking for.',
    'emptyIcon' => 'bi-inbox',
    'striped' => false,
    'hover' => true,
    'bordered' => false,
    'responsive' => true,
    'tableClass' => '',
])

@php
    $tableClasses = [
        'table',
        'align-middle',
        'mb-0',
        $striped ? 'table-striped' : '',
        $hover ? 'table-hover' : '',
        $bordered ? 'table-bordered' : '',
        $tableClass,
    ];
@endphp

@if ($responsive)
    <div class="table-responsive rounded-3">
@endif

    <table {{ $attributes->merge(['class' => implode(' ', array_filter($tableClasses))]) }}>
        @if (!empty($headers) || isset($thead))
            <thead class="table-light">
                @if (isset($thead))
                    {{ $thead }}
                @else
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col" class="py-3 px-3 text-muted fw-semibold text-uppercase small">
                                {{ _trans((string)$header) }}
                            </th>
                        @endforeach
                    </tr>
                @endif
            </thead>
        @endif

        <tbody>
            @if ($empty)
                <tr>
                    <td colspan="{{ max(count($headers), 1) }}" class="p-0 border-0">
                        @if (isset($emptyState))
                            {{ $emptyState }}
                        @else
                            <div class="text-center py-5 px-3">
                                <div class="mb-3">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-muted" style="width: 64px; height: 64px;">
                                        <i class="bi {{ $emptyIcon }} fs-2"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">{{ _trans($emptyMessage) }}</h6>
                                <p class="text-muted small mb-0">{{ _trans($emptySubtitle) }}</p>
                            </div>
                        @endif
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>

        @if (isset($tfoot))
            <tfoot class="table-light">
                {{ $tfoot }}
            </tfoot>
        @endif
    </table>

@if ($responsive)
    </div>
@endif
