@if ($paginator->hasPages())
    <nav class="d-flex justify-items-center justify-content-between">
        <div class="d-flex justify-content-between flex-fill d-sm-none">
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">@lang('pagination.previous')</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}"
                            rel="prev">@lang('pagination.previous')</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">@lang('pagination.next')</span>
                    </li>
                @endif
            </ul>
        </div>

        <div class="d-none flex-sm-fill d-sm-flex align-items-center justify-content-sm-between">
            <div class="d-flex align-items-center gap-3 me-4">
                <p class="small text-muted mb-0">
                    Mostrando
                    <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                    a
                    <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                    de
                    <span class="fw-semibold">{{ $paginator->total() }}</span>
                    resultados
                </p>

                {{-- Campo para ir a página específica --}}
                <div class="d-flex align-items-center gap-2">
                    <label for="goto-page" class="small text-muted mb-0" style="white-space: nowrap;">Ir a página:</label>
                    <input type="number" id="goto-page" class="form-control form-control-sm" style="width: 70px;" min="1"
                        max="{{ $paginator->lastPage() }}" placeholder="#" onkeypress="if(event.key === 'Enter') { 
                                        const page = parseInt(this.value);
                                        const lastPage = {{ $paginator->lastPage() }};
                                        if (page >= 1 && page <= lastPage) {
                                            const url = new URL(window.location.href);
                                            url.searchParams.set('page', page);
                                            window.location.href = url.toString();
                                        } else {
                                            alert('Por favor ingrese un número entre 1 y ' + lastPage);
                                            this.value = '';
                                        }
                                    }">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="
                                        const input = document.getElementById('goto-page');
                                        const page = parseInt(input.value);
                                        const lastPage = {{ $paginator->lastPage() }};
                                        if (page >= 1 && page <= lastPage) {
                                            const url = new URL(window.location.href);
                                            url.searchParams.set('page', page);
                                            window.location.href = url.toString();
                                        } else {
                                            alert('Por favor ingrese un número entre 1 y ' + lastPage);
                                            input.value = '';
                                        }
                                    ">
                        Ir
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <ul class="pagination mb-0">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true">&lsaquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                                aria-label="@lang('pagination.previous')">&lsaquo;</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"
                                aria-label="@lang('pagination.next')">&rsaquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true">&rsaquo;</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif