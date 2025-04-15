@if ($paginator->hasPages())
    <nav class="my-3">
        <ul class="pagination pagination-rounded justify-content-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link bg-light text-muted border-0 shadow-sm">&laquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link bg-white border-0 shadow-sm" wire:click="previousPage" rel="prev" aria-label="Previous">&laquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- Dots --}}
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link bg-light text-muted border-0 shadow-sm">{{ $element }}</span></li>
                @endif

                {{-- Page Number Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link bg-primary border-0 shadow-sm">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <button wire:click="gotoPage({{ $page }})" class="page-link bg-white border-0 shadow-sm">{{ $page }}</button>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link bg-white border-0 shadow-sm" wire:click="nextPage" rel="next" aria-label="Next">&raquo;</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link bg-light text-muted border-0 shadow-sm">&raquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
