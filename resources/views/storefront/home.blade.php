@extends('layouts.app')

@section('title', 'DayatGames — Temukan. Beli. Mainkan.')

@section('content')
    @php($hero = $featuredGames->first() ?? $latestGames->first())

    <section class="relative isolate overflow-hidden rounded-3xl border border-slate-800 bg-slate-950">
        @if($hero)
            <img src="{{ asset($hero->hero_image) }}" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover opacity-35">
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950 via-slate-950/85 to-slate-950/25"></div>
        @endif
        <div class="max-w-3xl px-6 py-20 sm:px-10 lg:py-28">
            <p class="mb-3 font-semibold uppercase tracking-[0.25em] text-cyan-300">Temukan. Beli. Mainkan.</p>
            <h1 class="text-4xl font-black tracking-tight text-white sm:text-6xl">{{ $hero?->title ?? 'Marketplace game digital pilihan' }}</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-300">{{ $hero?->short_description ?? 'Jelajahi katalog game PC pilihan dengan pengalaman belanja yang ringkas dan transparan.' }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('catalog.index') }}" class="rounded-xl bg-violet-600 px-5 py-3 font-semibold text-white hover:bg-violet-500">Jelajahi katalog</a>
                @if($hero)
                    <a href="{{ route('catalog.show', $hero) }}" class="rounded-xl border border-slate-600 bg-slate-950/60 px-5 py-3 font-semibold text-white hover:border-cyan-400">Lihat game</a>
                @endif
            </div>
        </div>
    </section>

    @if($featuredGames->isNotEmpty())
        <section class="mt-14" aria-labelledby="featured-heading">
            <div class="mb-6 flex items-end justify-between">
                <div><p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Sorotan</p><h2 id="featured-heading" class="text-3xl font-bold text-white">Featured games</h2></div>
                <a href="{{ route('catalog.index') }}" class="text-sm font-semibold text-cyan-300">Lihat semua →</a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($featuredGames->take(4) as $game)<x-game-card :game="$game" />@endforeach
            </div>
        </section>
    @endif

    @if($discountedGames->isNotEmpty())
        <section class="mt-14" aria-labelledby="discount-heading">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-300">Harga spesial</p>
            <h2 id="discount-heading" class="mb-6 text-3xl font-bold text-white">Game diskon</h2>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($discountedGames as $game)<x-game-card :game="$game" />@endforeach
            </div>
        </section>
    @endif

    <section class="mt-14" aria-labelledby="popular-heading">
        <p class="text-sm font-semibold uppercase tracking-widest text-cyan-300">Pilihan komunitas</p>
        <h2 id="popular-heading" class="mb-6 text-3xl font-bold text-white">Game populer</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($popularGames as $game)<x-game-card :game="$game" />@endforeach
        </div>
    </section>

    <section class="mt-14" aria-labelledby="latest-heading">
        <p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Katalog terkini</p>
        <h2 id="latest-heading" class="mb-6 text-3xl font-bold text-white">Rilis terbaru</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($latestGames as $game)<x-game-card :game="$game" />@endforeach
        </div>
    </section>

    <section class="mt-14 rounded-3xl border border-slate-800 bg-slate-900 p-7" aria-labelledby="genres-heading">
        <h2 id="genres-heading" class="text-2xl font-bold text-white">Jelajahi genre</h2>
        <div class="mt-5 flex flex-wrap gap-3">
            @foreach($genres as $genre)
                <a href="{{ route('catalog.index', ['genre' => $genre->slug]) }}" class="rounded-full border border-slate-700 px-4 py-2 text-slate-200 hover:border-cyan-400 hover:text-cyan-300">{{ $genre->name }} <span class="text-slate-500">({{ $genre->games_count }})</span></a>
            @endforeach
        </div>
    </section>

    <section class="mt-14 rounded-3xl bg-gradient-to-r from-violet-700 to-cyan-700 p-8 text-center sm:p-12">
        <h2 class="text-3xl font-black text-white">Siap menemukan game berikutnya?</h2>
        <p class="mx-auto mt-3 max-w-xl text-cyan-50">Daftar untuk menyimpan wishlist, mengelola cart, dan membangun library digital Anda.</p>
        <a href="{{ auth()->check() ? route('catalog.index') : route('register') }}" class="mt-6 inline-block rounded-xl bg-white px-5 py-3 font-bold text-slate-950">{{ auth()->check() ? 'Buka katalog' : 'Buat akun demo' }}</a>
    </section>
@endsection
