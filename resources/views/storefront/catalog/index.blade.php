@extends('layouts.app')

@section('title', 'Semua Game — DayatGames')

@section('content')
    <header class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-widest text-cyan-300">Katalog</p>
        <h1 class="text-4xl font-black text-white">Semua game</h1>
        <p class="mt-2 text-slate-400">Cari dan filter game published berdasarkan genre serta rentang harga.</p>
    </header>

    <form method="GET" action="{{ route('catalog.index') }}" class="mb-8 grid gap-4 rounded-2xl border border-slate-800 bg-slate-900 p-5 md:grid-cols-6">
        <label class="md:col-span-2"><span class="mb-1 block text-sm text-slate-300">Cari game</span><input name="search" value="{{ request('search') }}" maxlength="100" placeholder="Judul atau deskripsi" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white"></label>
        <label>
            <span class="mb-1 block text-sm text-slate-300">Genre</span>
            <select name="genre" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                <option value="">Semua</option>
                @foreach($genres as $genre)<option value="{{ $genre->slug }}" @selected(request('genre') === $genre->slug)>{{ $genre->name }}</option>@endforeach
            </select>
        </label>
        <label><span class="mb-1 block text-sm text-slate-300">Harga min.</span><input type="number" min="0" name="min_price" value="{{ request('min_price') }}" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white"></label>
        <label><span class="mb-1 block text-sm text-slate-300">Harga maks.</span><input type="number" min="0" name="max_price" value="{{ request('max_price') }}" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white"></label>
        <label>
            <span class="mb-1 block text-sm text-slate-300">Urutkan</span>
            <select name="sort" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                <option value="latest" @selected(request('sort', 'latest') === 'latest')>Terbaru</option>
                <option value="oldest" @selected(request('sort') === 'oldest')>Terlama</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Harga terendah</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Harga tertinggi</option>
                <option value="discount" @selected(request('sort') === 'discount')>Diskon terbesar</option>
            </select>
        </label>
        <div class="flex gap-3 md:col-span-6">
            <button class="rounded-lg bg-violet-600 px-4 py-2 font-semibold text-white hover:bg-violet-500">Terapkan filter</button>
            <a href="{{ route('catalog.index') }}" class="rounded-lg border border-slate-700 px-4 py-2 text-slate-300">Reset</a>
        </div>
    </form>

    @if($games->isEmpty())
        <x-empty-state title="Game tidak ditemukan" message="Coba ubah kata kunci atau hapus sebagian filter.">
            <a href="{{ route('catalog.index') }}" class="mt-5 inline-block text-cyan-300">Tampilkan semua game</a>
        </x-empty-state>
    @else
        <p class="mb-5 text-sm text-slate-400">Menampilkan {{ $games->firstItem() }}–{{ $games->lastItem() }} dari {{ $games->total() }} game.</p>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($games as $game)<x-game-card :game="$game" />@endforeach
        </div>
        <div class="mt-8">{{ $games->links() }}</div>
    @endif
@endsection
