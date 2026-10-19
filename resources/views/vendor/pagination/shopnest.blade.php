@if ($paginator->hasPages())
<div class="flex items-center justify-between gap-4 pt-1">

    {{-- Left: results info --}}
    <p class="text-sm text-gray-500 select-none">
        Showing
        <span class="font-semibold text-gray-800">{{ number_format($paginator->firstItem()) }}</span>
        &ndash;
        <span class="font-semibold text-gray-800">{{ number_format($paginator->lastItem()) }}</span>
        of
        <span class="font-semibold text-gray-800">{{ number_format($paginator->total()) }}</span>
        results
    </p>

    {{-- Right: controls --}}
    <div class="flex items-center gap-1">

        {{-- First page --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 cursor-not-allowed" title="First page">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->url(1) }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150"
               title="First page">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 cursor-not-allowed" title="Previous">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150"
               title="Previous">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif

        {{-- Page numbers --}}
        <div class="flex items-center gap-1 mx-1">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-400 select-none tracking-widest">
                        &hellip;
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-600 text-white text-sm font-semibold shadow-md shadow-indigo-200 select-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-600 text-sm font-medium hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 border border-transparent hover:border-indigo-100">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150"
               title="Next">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 cursor-not-allowed" title="Next">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        @endif

        {{-- Last page --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->url($paginator->lastPage()) }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150"
               title="Last page">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M6 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 cursor-not-allowed" title="Last page">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M6 5l7 7-7 7"/>
                </svg>
            </span>
        @endif

    </div>
</div>
@endif
