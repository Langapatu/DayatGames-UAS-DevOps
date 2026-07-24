@extends('layouts.app')

@section('title', $game->title.' — DayatGames')

@section('content')
    @php
        $currentPrice = (float) $game->currentPrice();
        $hasDiscount = $game->discount_price !== null && $game->discount_percent > 0;
    @endphp

    <article>
        <section class="relative isolate overflow-hidden rounded-3xl border border-slate-800 bg-slate-950">
            <img src="{{ asset($game->hero_image ?: $game->cover_image) }}" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover opacity-30">
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950 via-slate-950/80 to-slate-950/20"></div>
            <div class="grid items-end gap-8 px-6 py-12 md:grid-cols-[280px_1fr] md:px-10 md:py-16">
                <img src="{{ asset($game->cover_image) }}" alt="Cover {{ $game->title }}" class="game-image-contain aspect-[16/10] w-full rounded-2xl shadow-2xl">
                <div>
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach($game->genres as $genre)<a href="{{ route('catalog.index', ['genre' => $genre->slug]) }}" class="rounded-full bg-slate-800/80 px-3 py-1 text-xs text-slate-200">{{ $genre->name }}</a>@endforeach
                    </div>
                    <h1 class="text-4xl font-black text-white sm:text-5xl">{{ $game->title }}</h1>
                    <p class="mt-4 max-w-3xl text-lg leading-8 text-slate-300">{{ $game->short_description }}</p>
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        <div>
                            @if($hasDiscount)
                                <span class="mr-2 text-slate-400 line-through">Rp{{ number_format((float) $game->original_price, 0, ',', '.') }}</span>
                                <span class="rounded bg-emerald-500/20 px-2 py-1 text-sm font-bold text-emerald-300">-{{ $game->discount_percent }}%</span>
                            @endif
                            <strong class="mt-1 block text-3xl text-cyan-300">{{ $currentPrice === 0.0 ? 'Gratis' : 'Rp'.number_format($currentPrice, 0, ',', '.') }}</strong>
                            @if($game->price_is_demo)<small class="text-amber-300">Harga demonstrasi akademik</small>@endif
                        </div>
                        @auth
                            @if(! auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('cart.store', $game) }}">@csrf<button class="rounded-xl bg-violet-600 px-5 py-3 font-bold text-white hover:bg-violet-500">Tambahkan ke cart</button></form>
                                <form method="POST" action="{{ route('wishlist.store', $game) }}">@csrf<button class="rounded-xl border border-slate-600 bg-slate-950/70 px-5 py-3 font-bold text-white hover:border-cyan-400">Simpan wishlist</button></form>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl bg-violet-600 px-5 py-3 font-bold text-white">Login untuk membeli</a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-10 grid gap-8 lg:grid-cols-[1fr_320px]">
            <div class="space-y-10">
                <section><h2 class="text-2xl font-bold text-white">Tentang game</h2><div class="mt-4 whitespace-pre-line leading-8 text-slate-300">{{ $game->description }}</div></section>

                @if($game->images->isNotEmpty())
                    <section>
                        <h2 class="text-2xl font-bold text-white">Gallery</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">@foreach($game->images as $image)<img src="{{ asset($image->image_path) }}" alt="Screenshot {{ $game->title }}" class="rounded-xl" loading="lazy">@endforeach</div>
                    </section>
                @endif

                <section>
                    <h2 class="text-2xl font-bold text-white">Review pengguna</h2>
                    @forelse($game->reviews as $review)
                        <article class="mt-4 rounded-xl border border-slate-800 bg-slate-900 p-4">
                            <div class="flex justify-between"><strong class="text-white">{{ $review->user->name }}</strong><span class="text-amber-300" aria-label="{{ $review->rating }} dari 5 bintang">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span></div>
                            @if($review->comment)<p class="mt-2 text-slate-300">{{ $review->comment }}</p>@endif
                        </article>
                    @empty
                        <p class="mt-3 text-slate-400">Belum ada review yang dipublikasikan.</p>
                    @endforelse
                </section>
            </div>

            <aside class="h-fit rounded-2xl border border-slate-800 bg-slate-900 p-5">
                <h2 class="font-bold text-white">Informasi</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div><dt class="text-slate-500">Developer</dt><dd class="text-slate-200">{{ $game->developer->name }}</dd></div>
                    <div><dt class="text-slate-500">Publisher</dt><dd class="text-slate-200">{{ $game->publisher->name }}</dd></div>
                    <div><dt class="text-slate-500">Rilis</dt><dd class="text-slate-200">{{ $game->release_date?->translatedFormat('d F Y') ?? 'Belum diumumkan' }}</dd></div>
                    <div><dt class="text-slate-500">Platform</dt><dd class="text-slate-200">{{ $game->platform }}</dd></div>
                    <div><dt class="text-slate-500">Sistem operasi</dt><dd class="text-slate-200">{{ $game->operating_system ?: 'Lihat persyaratan resmi' }}</dd></div>
                </dl>
                @if($game->price_source_url)<a href="{{ $game->price_source_url }}" target="_blank" rel="noreferrer" class="mt-5 inline-block text-sm text-cyan-300">Sumber harga resmi ↗</a>@endif
            </aside>
        </div>

        @if($relatedGames->isNotEmpty())
            <section class="mt-14"><h2 class="mb-6 text-2xl font-bold text-white">Game terkait</h2><div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@foreach($relatedGames as $related)<x-game-card :game="$related" />@endforeach</div></section>
        @endif
    </article>
@endsection
