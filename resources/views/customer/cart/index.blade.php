@extends('layouts.app')

@section('title', 'Cart — DayatGames')

@section('content')
    <header class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-widest text-cyan-300">Belanja digital</p>
        <h1 class="text-4xl font-black text-white">Cart</h1>
    </header>

    @if($cart->items->isEmpty())
        <x-empty-state title="Cart masih kosong" message="Tambahkan game published yang belum Anda miliki.">
            <a href="{{ route('catalog.index') }}" class="mt-5 inline-block rounded-lg bg-violet-600 px-4 py-2 font-semibold text-white">Pilih game</a>
        </x-empty-state>
    @else
        <div class="grid gap-8 lg:grid-cols-[1fr_340px]">
            <div class="space-y-4">
                @foreach($cart->items as $item)
                    <article class="flex gap-4 rounded-2xl border border-slate-800 bg-slate-900 p-4">
                        <img src="{{ asset($item->game->cover_image) }}" alt="Cover {{ $item->game->title }}" class="h-24 w-36 rounded-xl object-cover">
                        <div class="min-w-0 flex-1">
                            <h2 class="font-bold text-white"><a href="{{ route('catalog.show', $item->game) }}">{{ $item->game->title }}</a></h2>
                            <p class="text-sm text-slate-400">{{ $item->game->developer->name }}</p>
                            <strong class="mt-3 block text-cyan-300">{{ (float) $item->price === 0.0 ? 'Gratis' : 'Rp'.number_format((float) $item->price, 0, ',', '.') }}</strong>
                        </div>
                        <form method="POST" action="{{ route('cart.destroy', $item->game) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-red-300 hover:text-red-200">Hapus</button>
                        </form>
                    </article>
                @endforeach
            </div>
            <aside class="h-fit rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <h2 class="text-xl font-bold text-white">Ringkasan</h2>
                <div class="mt-5 flex justify-between text-slate-300"><span>{{ $cart->items->count() }} game</span><strong>Rp{{ number_format((float) $cart->items->sum('price'), 0, ',', '.') }}</strong></div>
                <p class="mt-3 text-xs leading-5 text-slate-500">Harga akan dihitung ulang dari database ketika checkout.</p>
                @if(Route::has('checkout.create'))
                    <a href="{{ route('checkout.create') }}" class="mt-6 block rounded-xl bg-violet-600 px-4 py-3 text-center font-bold text-white hover:bg-violet-500">Lanjut checkout</a>
                @endif
            </aside>
        </div>
    @endif
@endsection
