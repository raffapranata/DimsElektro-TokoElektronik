@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginasi" class="flex items-center justify-center gap-1.5">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="grid size-9 place-items-center border-2 border-zinc-300 text-zinc-300" aria-hidden="true">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <path d="M15 6l-6 6 6 6" stroke-linecap="square" stroke-linejoin="round" />
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
               class="grid size-9 place-items-center border-2 border-zinc-900 bg-white text-zinc-900 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all hover:-translate-y-0.5 hover:bg-amber-100 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <path d="M15 6l-6 6 6 6" stroke-linecap="square" stroke-linejoin="round" />
                </svg>
            </a>
        @endif

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
               class="grid size-9 place-items-center border-2 border-zinc-900 bg-white text-zinc-900 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all hover:-translate-y-0.5 hover:bg-amber-100 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <path d="M9 6l6 6-6 6" stroke-linecap="square" stroke-linejoin="round" />
                </svg>
            </a>
        @else
            <span class="grid size-9 place-items-center border-2 border-zinc-300 text-zinc-300" aria-hidden="true">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <path d="M9 6l6 6-6 6" stroke-linecap="square" stroke-linejoin="round" />
                </svg>
            </span>
        @endif
    </nav>
@endif
