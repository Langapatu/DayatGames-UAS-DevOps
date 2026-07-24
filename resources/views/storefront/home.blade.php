@extends('layouts.app')

@section('title', 'DayatGames — Temukan. Beli. Mainkan.')

@section('content')
    @php($heroGames = $featuredGames->isNotEmpty() ? $featuredGames : $latestGames->take(5))

    <section data-hero data-home-hero class="home-hero relative isolate overflow-hidden rounded-3xl border border-slate-800 bg-slate-950">
        <div class="home-hero-swiper swiper">
            <div class="swiper-wrapper">
                @forelse($heroGames as $game)
                    @php($heroPreview = $game->images->first()?->image_path)
                    <article data-hero-slide class="home-hero-slide swiper-slide">
                        <img
                            data-hero-image
                            src="{{ $heroPreview ? asset($heroPreview) : $game->heroUrl() }}"
                            alt=""
                            class="home-hero-image"
                            @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
                        >
                        <div class="home-hero-shade"></div>
                        <div class="home-hero-content">
                            <p data-hero-item class="mb-3 font-semibold uppercase tracking-[0.25em] text-cyan-300">Temukan. Beli. Mainkan.</p>
                            <h1 data-hero-item class="text-4xl font-black tracking-tight text-white sm:text-6xl">{{ $game->title }}</h1>
                            <p data-hero-item class="mt-5 max-w-2xl text-lg leading-8 text-slate-300">{{ $game->short_description }}</p>
                            <div data-hero-item class="mt-8 flex flex-wrap gap-3">
                                <a data-interactive-button href="{{ route('catalog.index') }}" class="rounded-xl bg-violet-600 px-5 py-3 font-semibold text-white hover:bg-violet-500">Jelajahi katalog</a>
                                <a href="{{ route('catalog.show', $game) }}" class="rounded-xl border border-slate-600 bg-slate-950/60 px-5 py-3 font-semibold text-white hover:border-cyan-400">Lihat game</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <article data-hero-slide class="home-hero-slide swiper-slide">
                        <div class="home-hero-shade"></div>
                        <div class="home-hero-content">
                            <p data-hero-item class="mb-3 font-semibold uppercase tracking-[0.25em] text-cyan-300">Temukan. Beli. Mainkan.</p>
                            <h1 data-hero-item class="text-4xl font-black tracking-tight text-white sm:text-6xl">Marketplace game digital pilihan</h1>
                            <p data-hero-item class="mt-5 max-w-2xl text-lg leading-8 text-slate-300">Jelajahi katalog game PC pilihan dengan pengalaman belanja yang ringkas dan transparan.</p>
                            <div data-hero-item class="mt-8">
                                <a data-interactive-button href="{{ route('catalog.index') }}" class="rounded-xl bg-violet-600 px-5 py-3 font-semibold text-white hover:bg-violet-500">Jelajahi katalog</a>
                            </div>
                        </div>
                    </article>
                @endforelse
            </div>
        </div>

        @if($heroGames->count() > 1)
            <div class="home-hero-controls">
                <button data-hero-prev type="button" class="home-hero-arrow" aria-label="Game hero sebelumnya">←</button>
                <div data-hero-pagination class="home-hero-pagination" aria-label="Pilih game hero"></div>
                <button data-hero-next type="button" class="home-hero-arrow" aria-label="Game hero berikutnya">→</button>
            </div>
        @endif
    </section>

    @if($featuredGames->isNotEmpty())
        <section data-reveal class="mt-14" aria-labelledby="featured-heading">
            <div class="mb-6 flex items-end justify-between">
                <div><p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Sorotan</p><h2 id="featured-heading" class="text-3xl font-bold text-white">Featured games</h2></div>
                <div class="flex items-center gap-2">
                    <button type="button" class="featured-prev carousel-button" aria-label="Game sebelumnya">←</button>
                    <button type="button" class="featured-next carousel-button" aria-label="Game berikutnya">→</button>
                    <a href="{{ route('catalog.index') }}" class="ml-2 text-sm font-semibold text-cyan-300">Lihat semua</a>
                </div>
            </div>
            <div class="featured-swiper">
                <div class="swiper-wrapper">
                    @foreach($featuredGames as $game)<div class="swiper-slide"><x-game-card :game="$game" /></div>@endforeach
                </div>
            </div>
        </section>
    @endif

    @if($discountedGames->isNotEmpty())
        <section data-reveal class="mt-14" aria-labelledby="discount-heading">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-300">Harga spesial</p>
            <h2 id="discount-heading" class="mb-6 text-3xl font-bold text-white">Game diskon</h2>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($discountedGames as $game)<x-game-card :game="$game" />@endforeach
            </div>
        </section>
    @endif

    <section data-reveal class="mt-14" aria-labelledby="popular-heading">
        <p class="text-sm font-semibold uppercase tracking-widest text-cyan-300">Pilihan komunitas</p>
        <h2 id="popular-heading" class="mb-6 text-3xl font-bold text-white">Game populer</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($popularGames as $game)<x-game-card :game="$game" />@endforeach
        </div>
    </section>

    <section data-reveal class="mt-14" aria-labelledby="latest-heading">
        <p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Katalog terkini</p>
        <h2 id="latest-heading" class="mb-6 text-3xl font-bold text-white">Rilis terbaru</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($latestGames as $game)<x-game-card :game="$game" />@endforeach
        </div>
    </section>

    <section data-reveal class="mt-14 rounded-3xl border border-slate-800 bg-slate-900 p-7" aria-labelledby="genres-heading">
        <h2 id="genres-heading" class="text-2xl font-bold text-white">Jelajahi genre</h2>
        <div class="mt-5 flex flex-wrap gap-3">
            @foreach($genres as $genre)
                <a href="{{ route('catalog.index', ['genre' => $genre->slug]) }}" class="rounded-full border border-slate-700 px-4 py-2 text-slate-200 hover:border-cyan-400 hover:text-cyan-300">{{ $genre->name }} <span class="text-slate-500">({{ $genre->games_count }})</span></a>
            @endforeach
        </div>
    </section>

    <section data-reveal class="mt-14 rounded-3xl bg-gradient-to-r from-sky-700 to-cyan-700 p-8 text-center sm:p-12">
        <h2 class="text-3xl font-black text-white">Siap menemukan game berikutnya?</h2>
        <p class="mx-auto mt-3 max-w-xl text-cyan-50">Daftar untuk menyimpan wishlist, mengelola cart, dan membangun library digital Anda.</p>
        <a href="{{ auth()->check() ? route('catalog.index') : route('register') }}" class="mt-6 inline-block rounded-xl bg-white px-5 py-3 font-bold text-slate-950">{{ auth()->check() ? 'Buka katalog' : 'Buat akun demo' }}</a>
    </section>
@endsection
