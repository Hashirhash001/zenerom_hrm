@php
    $currentPage = $pagination->currentPage();
    $lastPage = $pagination->lastPage();
    $delta = 1; // Show 1 page before and after current page
    $pagesToShow = [$currentPage];
    if ($lastPage > 1) {
        // Add first page
        $pagesToShow[] = 1;
        // Add pages around current page
        for ($i = max(2, $currentPage - $delta); $i <= min($lastPage - 1, $currentPage + $delta); $i++) {
            if (!in_array($i, $pagesToShow)) {
                $pagesToShow[] = $i;
            }
        }
        // Add last page
        $pagesToShow[] = $lastPage;
        // Sort and remove duplicates
        $pagesToShow = array_unique($pagesToShow);
        sort($pagesToShow);
        // Add ellipses
        $finalPages = [];
        $previous = null;
        foreach ($pagesToShow as $page) {
            if ($previous && $page - $previous > 1) {
                $finalPages[] = '...';
            }
            $finalPages[] = $page;
            $previous = $page;
        }
        $pagesToShow = $finalPages;
    }
@endphp
<div class="mt-4">
    <nav aria-label="Page navigation">
        <ul class="pagination flex flex-wrap justify-center gap-1">
            <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                <a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#" data-page="{{ $currentPage - 1 }}">Previous</a>
            </li>
            @foreach($pagesToShow as $page)
                @if($page === '...')
                    <li class="page-item disabled"><span class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm">...</span></li>
                @else
                    <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                        <a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#" data-page="{{ $page }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach
            <li class="page-item {{ $currentPage == $lastPage ? 'disabled' : '' }}">
                <a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#" data-page="{{ $currentPage + 1 }}">Next</a>
            </li>
        </ul>
    </nav>
</div>
