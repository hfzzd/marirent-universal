@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2.5 text-[12px] font-medium text-gray-400 bg-gray-50 border border-gray-100 cursor-default rounded-xl">
                    <i class="fas fa-chevron-left text-[10px] mr-1.5"></i> Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2.5 text-[12px] font-medium text-sky-700 bg-white border border-sky-200 rounded-xl hover:bg-sky-50 transition-all duration-200 shadow-sm">
                    <i class="fas fa-chevron-left text-[10px] mr-1.5"></i> Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2.5 ml-3 text-[12px] font-medium text-sky-700 bg-white border border-sky-200 rounded-xl hover:bg-sky-50 transition-all duration-200 shadow-sm">
                    Selanjutnya <i class="fas fa-chevron-right text-[10px] ml-1.5"></i>
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2.5 ml-3 text-[12px] font-medium text-gray-400 bg-gray-50 border border-gray-100 cursor-default rounded-xl">
                    Selanjutnya <i class="fas fa-chevron-right text-[10px] ml-1.5"></i>
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-[13px] text-gray-400">
                    Menampilkan
                    @if ($paginator->firstItem())
                        <span class="font-semibold text-navy-800">{{ $paginator->firstItem() }}</span>
                        -
                        <span class="font-semibold text-navy-800">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    dari
                    <span class="font-semibold text-navy-800">{{ $paginator->total() }}</span> produk
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex items-center gap-1 shadow-sm rounded-xl">
                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" class="relative inline-flex items-center px-3 py-2 text-[12px] font-medium text-gray-400 bg-gray-50 border border-gray-100 cursor-default rounded-xl">
                            <i class="fas fa-chevron-left text-[10px]"></i>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 text-[12px] font-medium text-sky-700 bg-white border border-sky-200 rounded-xl hover:bg-sky-50 transition-all duration-200 shadow-sm">
                            <i class="fas fa-chevron-left text-[10px]"></i>
                        </a>
                    @endif

                    {{-- Pages --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-3 py-2 text-[12px] font-medium text-gray-400 bg-gray-50 border border-gray-100 cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-3.5 py-2 text-[12px] font-bold text-white bg-sky-600 border border-sky-600 rounded-xl shadow-sm shadow-sky-500/25">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-3.5 py-2 text-[12px] font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-sky-50 hover:border-sky-200 hover:text-sky-700 transition-all duration-200">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 text-[12px] font-medium text-sky-700 bg-white border border-sky-200 rounded-xl hover:bg-sky-50 transition-all duration-200 shadow-sm">
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                    @else
                        <span aria-disabled="true" class="relative inline-flex items-center px-3 py-2 text-[12px] font-medium text-gray-400 bg-gray-50 border border-gray-100 cursor-default rounded-xl">
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
