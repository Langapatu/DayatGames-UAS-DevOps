@props(['game'])

@php
    $previewImages = $game->images
        ->map(fn ($image) => [
            'url' => asset($image->image_path),
            'alt' => 'Screenshot '.$game->title,
        ])
        ->values();

    if ($previewImages->isEmpty()) {
        $previewImages = collect([[
            'url' => $game->heroUrl(),
            'alt' => 'Preview '.$game->title,
        ]]);
    }

    $previewCount = $previewImages->count();
@endphp

<section
    data-preview-gallery
    data-preview-count="{{ $previewCount }}"
    class="game-preview"
    aria-labelledby="preview-heading"
>
    <div class="game-preview-heading">
        <div>
            <p class="game-preview-eyebrow">Lihat lebih dekat</p>
            <h2 id="preview-heading">Preview game</h2>
        </div>
        <p class="game-preview-hint">
            <span data-preview-current>1</span> / {{ $previewCount }}
            <span aria-hidden="true">·</span>
            Klik gambar untuk memperbesar
        </p>
    </div>

    <div class="game-preview-stage">
        <div data-preview-main class="game-preview-main swiper">
            <div class="swiper-wrapper">
                @foreach($previewImages as $index => $preview)
                    <div data-preview-slide class="swiper-slide">
                        <button
                            data-preview-open
                            data-preview-index="{{ $index }}"
                            type="button"
                            class="game-preview-open"
                            aria-label="Perbesar screenshot {{ $index + 1 }} dari {{ $previewCount }} untuk {{ $game->title }}"
                        >
                            <img
                                src="{{ $preview['url'] }}"
                                alt="{{ $preview['alt'] }} {{ $index + 1 }}"
                                @if($index > 0) loading="lazy" @else fetchpriority="high" @endif
                            >
                            <span class="game-preview-zoom" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="m15.5 15.5 4 4m-2-9a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M10.5 7.5v6m-3-3h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                Perbesar
                            </span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        @if($previewCount > 1)
            <button type="button" class="preview-prev preview-arrow preview-arrow-left" aria-label="Screenshot sebelumnya">
                <span aria-hidden="true">←</span>
            </button>
            <button type="button" class="preview-next preview-arrow preview-arrow-right" aria-label="Screenshot berikutnya">
                <span aria-hidden="true">→</span>
            </button>
            <div class="game-preview-progress" aria-hidden="true"><span data-preview-progress></span></div>
        @endif
    </div>

    @if($previewCount > 1)
        <div data-preview-thumbs class="game-preview-thumbs swiper" aria-label="Pilih screenshot">
            <div class="swiper-wrapper">
                @foreach($previewImages as $index => $preview)
                    <button
                        type="button"
                        class="swiper-slide game-preview-thumb"
                        aria-label="Tampilkan screenshot {{ $index + 1 }}"
                    >
                        <img src="{{ $preview['url'] }}" alt="" loading="lazy">
                        <span>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <dialog data-preview-lightbox class="preview-lightbox" aria-label="Tampilan screenshot {{ $game->title }}">
        <div class="preview-lightbox-shell">
            <div class="preview-lightbox-header">
                <div>
                    <p>Preview {{ $game->title }}</p>
                    <span><span data-lightbox-current>1</span> / {{ $previewCount }}</span>
                </div>
                <button type="button" data-lightbox-close class="preview-lightbox-close" aria-label="Tutup tampilan screenshot">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <div class="preview-lightbox-stage">
                <img
                    data-lightbox-image
                    src="{{ $previewImages->first()['url'] }}"
                    alt="{{ $previewImages->first()['alt'] }} 1"
                >

                @if($previewCount > 1)
                    <button type="button" data-lightbox-prev class="preview-arrow preview-arrow-left" aria-label="Screenshot sebelumnya">
                        <span aria-hidden="true">←</span>
                    </button>
                    <button type="button" data-lightbox-next class="preview-arrow preview-arrow-right" aria-label="Screenshot berikutnya">
                        <span aria-hidden="true">→</span>
                    </button>
                @endif
            </div>

            @if($previewCount > 1)
                <div class="preview-lightbox-thumbs" aria-label="Pilih screenshot layar penuh">
                    @foreach($previewImages as $index => $preview)
                        <button
                            type="button"
                            data-lightbox-thumbnail
                            data-preview-index="{{ $index }}"
                            @if($index === 0) aria-current="true" @endif
                            aria-label="Buka screenshot {{ $index + 1 }}"
                        >
                            <img src="{{ $preview['url'] }}" alt="" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </dialog>
</section>
