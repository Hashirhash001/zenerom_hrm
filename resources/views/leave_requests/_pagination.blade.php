<!-- resources/views/leave_requests/_pagination.blade.php -->
<div class="mt-4 mb-4">
    <nav aria-label="Page navigation">
        <ul class="pagination flex flex-wrap justify-center gap-1">
            @if ($pagination->onFirstPage())
                <li class="page-item disabled"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#">Previous</a></li>
            @else
                <li class="page-item"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="{{ $pagination->previousPageUrl() }}">Previous</a></li>
            @endif
            @foreach ($pagination->getUrlRange(1, $pagination->lastPage()) as $page => $url)
                @if ($page == $pagination->currentPage())
                    <li class="page-item active"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#">{{ $page }}</a></li>
                @else
                    <li class="page-item"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach
            @if ($pagination->hasMorePages())
                <li class="page-item"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="{{ $pagination->nextPageUrl() }}">Next</a></li>
            @else
                <li class="page-item disabled"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#">Next</a></li>
            @endif
        </ul>
    </nav>
</div>
