<div class="mt-4">
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

            @foreach ($pagination->getUrlRange(1, $pagination->lastPage()) as $page => $url)
                @if ($page == $pagination->currentPage())
                    <li class="page-item active">
                        <span class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="{{ $url }}">{{ $page }}</a>
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
