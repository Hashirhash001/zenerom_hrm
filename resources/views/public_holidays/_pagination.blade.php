@php
    $currentPage = $pagination->currentPage();
    $lastPage = $pagination->lastPage();
    $delta = 1;
    $pagesToShow = [$currentPage];

    // Add first page
    if ($currentPage > 1) {
        $pagesToShow[] = 1;
    }

    // Add pages around current page
    for ($i = max(2, $currentPage - $delta); $i <= min($lastPage - 1, $currentPage + $delta); $i++) {
        if ($i !== $currentPage) {
            $pagesToShow[] = $i;
        }
    }

    // Add last page
    if ($lastPage > 1 && $currentPage < $lastPage) {
        $pagesToShow[] = $lastPage;
    }

    // Sort and add ellipses
    $pagesToShow = array_unique($pagesToShow);
    sort($pagesToShow);
    $finalPages = [];
    $prevPage = null;
    foreach ($pagesToShow as $page) {
        if ($prevPage && $page > $prevPage + 1) {
            $finalPages[] = '...';
        }
        $finalPages[] = $page;
        $prevPage = $page;
    }
@endphp

<div class="mt-4 mb-4">
    <nav aria-label="Page navigation">
        <ul class="pagination flex flex-wrap justify-center gap-1">
            @if ($pagination->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm">Previous</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="{{ $pagination->previousPageUrl() }}">Previous</a>
                </li>
            @endif

            @foreach ($finalPages as $page)
                @if ($page === '...')
                    <li class="page-item disabled">
                        <span class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm">...</span>
                    </li>
                @else
                    <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                        <a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="{{ $pagination->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            @if ($pagination->hasMorePages())
                <li class="page-item">
                    <a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="{{ $pagination->nextPageUrl() }}">Next</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm">Next</span>
                </li>
            @endif
        </ul>
    </nav>
</div>
