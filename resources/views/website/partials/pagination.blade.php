@if ($paginator->hasPages())
    <nav class="shop-pagination" aria-label="Product pagination">
        @if ($paginator->onFirstPage())
            <span class="page-link disabled" aria-disabled="true"><i class="fas fa-chevron-left"></i></span>
        @else
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"><i class="fas fa-chevron-left"></i></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-link dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-link active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"><i class="fas fa-chevron-right"></i></a>
        @else
            <span class="page-link disabled" aria-disabled="true"><i class="fas fa-chevron-right"></i></span>
        @endif
    </nav>
@endif
