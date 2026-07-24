@props(['game'])

@php
    $currentPrice = (float) $game->currentPrice();
    $hasDiscount = $game->discount_price !== null && $game->discount_percent > 0;
@endphp

<article data-game-card class="group h-full overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
    <a href="{{ route('catalog.show', $game) }}" class="game-media-frame">
        <img src="{{ asset($game->cover_image ?: 'images/brand/dayatgames-logo.png') }}"
             alt="Cover {{ $game->title }}"
             loading="lazy">
    </a>
    <div class="space-y-3 p-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="font-semibold text-slate-50">
                    <a href="{{ route('catalog.show', $game) }}">{{ $game->title }}</a>
                </h3>
                <p class="text-sm text-slate-400">{{ $game->developer?->name }}</p>
            </div>
            @if($hasDiscount)
                <span class="rounded-full bg-emerald-500/15 px-2 py-1 text-xs font-bold text-emerald-300">-{{ $game->discount_percent }}%</span>
            @endif
        </div>

        <div class="flex flex-wrap gap-1" aria-label="Genre">
            @foreach($game->genres->take(2) as $genre)
                <a href="{{ route('catalog.index', ['genre' => $genre->slug]) }}" class="rounded bg-slate-800 px-2 py-1 text-xs text-slate-300">{{ $genre->name }}</a>
            @endforeach
        </div>

        <div class="flex items-end justify-between gap-3">
            <div>
                @if($hasDiscount)
                    <span class="block text-xs text-slate-500 line-through">Rp{{ number_format((float) $game->original_price, 0, ',', '.') }}</span>
                @endif
                <strong class="text-cyan-300">{{ $currentPrice === 0.0 ? 'Gratis' : 'Rp'.number_format($currentPrice, 0, ',', '.') }}</strong>
            </div>

            @auth
                @if(! auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('cart.store', $game) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-violet-600 px-3 py-2 text-sm font-semibold text-white hover:bg-violet-500">+ Cart</button>
                    </form>
                @endif
            @endauth
        </div>
    </div>
</article>
