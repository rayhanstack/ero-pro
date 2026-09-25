@props([
    'paginator' => null,
])

<div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 pt-3">
    @if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator || $paginator instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div class="text-muted small">
            {{ _trans('common.Showing') }}
            <span class="fw-semibold text-dark">{{ $paginator->firstItem() ?? 0 }}</span>
            {{ _trans('common.to') }}
            <span class="fw-semibold text-dark">{{ $paginator->lastItem() ?? 0 }}</span>
            @if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                {{ _trans('common.of') }}
                <span class="fw-semibold text-dark">{{ $paginator->total() }}</span>
                {{ _trans('common.results') }}
            @endif
        </div>
        <div class="pagination-wrapper">
            {{ $paginator->links('pagination::bootstrap-5') }}
        </div>
    @elseif (isset($slot) && $slot->isNotEmpty())
        {{ $slot }}
    @else
        <div class="text-muted small">
            {{ _trans('common.Showing') }} <span class="fw-semibold text-dark">1</span> {{ _trans('common.to') }} <span class="fw-semibold text-dark">10</span> {{ _trans('common.of') }} <span class="fw-semibold text-dark">50</span> {{ _trans('common.results') }}
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">&laquo;</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
            </ul>
        </nav>
    @endif
</div>
