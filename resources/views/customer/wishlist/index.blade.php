@extends('layouts.app')

@section('title', 'Wishlist Saya — DayatGames')

@section('content')
    <header class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Koleksi tersimpan</p>
        <h1 class="text-4xl font-black text-white">Wishlist saya</h1>
    </header>

    @if($wishlists->isEmpty())
        <x-empty-state title="Wishlist masih kosong" message="Simpan game yang menarik agar mudah ditemukan kembali.">
            <a href="{{ route('catalog.index') }}" class="mt-5 inline-block rounded-lg bg-violet-600 px-4 py-2 font-semibold text-white">Jelajahi katalog</a>
        </x-empty-state>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($wishlists as $wishlist)
                <div>
                    <x-game-card :game="$wishlist->game" />
                    <form method="POST" action="{{ route('wishlist.destroy', $wishlist->game) }}" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button class="w-full rounded-lg border border-red-500/40 px-3 py-2 text-sm text-red-300 hover:bg-red-500/10">Hapus dari wishlist</button>
                    </form>
                </div>
            @endforeach
        </div>
        <div class="mt-8">{{ $wishlists->links() }}</div>
    @endif
@endsection
