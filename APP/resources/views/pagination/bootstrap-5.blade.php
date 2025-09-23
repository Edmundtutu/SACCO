@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center mb-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link d-flex align-items-center gap-1">
                        <i class="bi bi-chevron-left"></i>
                        <span class="d-none d-sm-inline">Previous</span>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link d-flex align-items-center gap-1" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="bi bi-chevron-left"></i>
                        <span class="d-none d-sm-inline">Previous</span>
                    </a>
                </li>
            @endif

            {{-- First Page --}}
            @if ($paginator->currentPage() > 2)
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                </li>
                @if ($paginator->currentPage() > 3)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        {{-- Show only relevant pages around current page --}}
                        @if ($page >= $paginator->currentPage() - 1 && $page <= $paginator->currentPage() + 1)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Last Page --}}
            @if ($paginator->currentPage() < $paginator->lastPage() - 1)
                @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link d-flex align-items-center gap-1" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <span class="d-none d-sm-inline">Next</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link d-flex align-items-center gap-1">
                        <span class="d-none d-sm-inline">Next</span>
                        <i class="bi bi-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif