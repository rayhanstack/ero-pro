@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => '',
])

<div {{ $attributes->merge(['class' => 'card shadow-sm border-0 mb-4']) }}>
    @if ($title || isset($header) || isset($actions) || isset($tools))
        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between p-3 p-md-4 {{ $headerClass }}">
            @if (isset($header))
                {{ $header }}
            @else
                <div>
                    <h5 class="card-title fw-bold mb-0 d-flex align-items-center gap-2">
                        @if ($icon)
                            <i class="bi {{ $icon }} text-primary"></i>
                        @endif
                        <span>{{ _trans($title) }}</span>
                    </h5>
                    @if ($subtitle)
                        <p class="text-muted small mb-0 mt-1">{{ _trans($subtitle) }}</p>
                    @endif
                </div>
            @endif

            @if (isset($actions) || isset($tools))
                <div class="card-tools d-flex align-items-center gap-2">
                    {{ $actions ?? $tools }}
                </div>
            @endif
        </div>
    @endif

    <div class="card-body p-3 p-md-4 {{ $bodyClass }}">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="card-footer bg-light bg-opacity-50 border-top p-3 {{ $footerClass }}">
            {{ $footer }}
        </div>
    @endif
</div>
