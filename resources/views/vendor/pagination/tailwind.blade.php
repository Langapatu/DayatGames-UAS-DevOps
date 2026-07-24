@if ($paginator->hasPages())
    <nav class="dg-pagination" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <div class="dg-pagination-mobile">
            @if ($paginator->onFirstPage())
                <span class="is-disabled">{{ __('Previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" data-pagination-direction="previous">{{ __('Previous') }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" data-pagination-direction="next">{{ __('Next') }}</a>
            @else
                <span class="is-disabled">{{ __('Next') }}</span>
            @endif
        </div>

        <div class="dg-pagination-desktop">
            <p>
                Menampilkan
                <strong>{{ $paginator->firstItem() }}</strong>
                sampai
                <strong>{{ $paginator->lastItem() }}</strong>
                dari
                <strong>{{ $paginator->total() }}</strong>
                data
            </p>

            <span class="dg-pagination-pages">
                @if ($paginator->onFirstPage())
                    <span class="dg-page-arrow is-disabled" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
                    </span>
                @else
                    <a class="dg-page-arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev" data-pagination-direction="previous" aria-label="{{ __('pagination.previous') }}">
                        <svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="dg-page-ellipsis">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="dg-page-number is-current" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="dg-page-number"
                                   href="{{ $url }}"
                                   data-pagination-direction="{{ $page > $paginator->currentPage() ? 'next' : 'previous' }}"
                                   aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a class="dg-page-arrow" href="{{ $paginator->nextPageUrl() }}" rel="next" data-pagination-direction="next" aria-label="{{ __('pagination.next') }}">
                        <svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                @else
                    <span class="dg-page-arrow is-disabled" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
