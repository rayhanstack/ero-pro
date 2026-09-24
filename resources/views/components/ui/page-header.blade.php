@props([
    'title' => '',
    'subtitle' => null,
    'breadcrumbs' => [],
])

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        @if (!empty($breadcrumbs) && is_array($breadcrumbs))
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">
                            <i class="bi bi-house-door me-1"></i>{{ _trans('common.Home') }}
                        </a>
                    </li>
                    @foreach ($breadcrumbs as $item)
                        @if (is_array($item))
                            @if (!empty($item['url']) && !$loop->last)
                                <li class="breadcrumb-item">
                                    <a href="{{ $item['url'] }}" class="text-decoration-none text-muted">
                                        {{ _trans($item['label'] ?? '') }}
                                    </a>
                                </li>
                            @else
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ _trans($item['label'] ?? '') }}
                                </li>
                            @endif
                        @else
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                {{ _trans((string)$item) }}
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif

        <h3 class="fw-bold mb-0 text-dark">{{ _trans($title) }}</h3>
        @if ($subtitle)
            <p class="text-muted small mb-0 mt-1">{{ _trans($subtitle) }}</p>
        @endif
    </div>

    @if (isset($action) || isset($actions) || $slot->isNotEmpty())
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{ $action ?? ($actions ?? $slot) }}
        </div>
    @endif
</div>
