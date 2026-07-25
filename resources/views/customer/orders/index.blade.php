@extends('layouts.app')

@section('title', 'Lacak Pesanan — DayatGames')

@section('content')
    <header class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-widest text-cyan-300">Transaksi saya</p>
        <h1 class="text-4xl font-black text-white">Lacak Pesanan</h1>
        <p class="mt-2 max-w-2xl text-slate-400">Cari kode pesanan dan lanjutkan pembayaran tanpa membuat order berulang.</p>
    </header>

    <form method="GET" action="{{ route('orders.index') }}" class="mb-7 grid gap-3 rounded-2xl border border-slate-800 bg-slate-900/80 p-4 sm:grid-cols-[minmax(0,1fr)_190px_auto]">
        <label class="block">
            <span class="sr-only">Cari kode pesanan</span>
            <input name="search" type="search" value="{{ request('search') }}" placeholder="Cari kode, contoh DG-2026..." class="h-11 w-full rounded-xl border border-slate-700 bg-slate-950 px-4 text-white">
        </label>
        <label class="block">
            <span class="sr-only">Filter status</span>
            <select name="status" class="h-11 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 text-white">
                <option value="">Semua status</option>
                <option value="pending" @selected(request('status') === 'pending')>Berjalan</option>
                <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
            </select>
        </label>
        <button class="h-11 rounded-xl bg-violet-600 px-5 font-bold text-white hover:bg-violet-500">Tampilkan</button>
    </form>

    @if($orders->isEmpty())
        <x-empty-state title="Pesanan tidak ditemukan" message="Coba ubah kode pencarian atau status pesanan.">
            <a href="{{ route('catalog.index') }}" class="mt-5 inline-block text-cyan-300">Jelajahi game</a>
        </x-empty-state>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($orders as $order)
                @php
                    $firstItem = $order->items->first();
                    $additionalItems = max(0, $order->items_count - 1);
                @endphp
                <article data-order-card class="group rounded-2xl border border-slate-800 bg-slate-900/90 p-4 transition hover:-translate-y-0.5 hover:border-cyan-500/30">
                    <div class="flex min-w-0 gap-4">
                        @if($firstItem?->game)
                            <img src="{{ $firstItem->game->coverUrl() }}" alt="Cover {{ $firstItem->game_title }}" class="h-28 w-[5.6rem] shrink-0 rounded-xl border border-slate-700 bg-slate-950 object-contain">
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="font-mono text-xs text-cyan-300">{{ $order->order_code }}</p>
                                    <h2 class="mt-1 truncate font-bold text-white">{{ $firstItem?->game_title ?: 'Pesanan DayatGames' }}</h2>
                                    @if($additionalItems > 0)
                                        <p class="mt-1 text-xs text-slate-500">+{{ $additionalItems }} game lainnya</p>
                                    @endif
                                </div>
                                <span class="rounded-full bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-200">{{ $order->trackingStage() }}</span>
                            </div>
                            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div><dt class="text-xs text-slate-500">Dibuat</dt><dd class="mt-1 text-slate-300">{{ $order->ordered_at->format('d/m/Y H:i') }}</dd></div>
                                <div><dt class="text-xs text-slate-500">Total</dt><dd class="mt-1 font-bold text-white">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</dd></div>
                            </dl>
                        </div>
                    </div>
                    <a href="{{ route('orders.show', $order) }}" class="mt-4 flex items-center justify-between rounded-xl border border-slate-700 px-4 py-3 text-sm font-bold text-slate-200 transition group-hover:border-cyan-500/30 group-hover:text-cyan-200">
                        <span>Lihat detail & pembayaran</span><span aria-hidden="true">→</span>
                    </a>
                </article>
            @endforeach
        </div>
        <div class="mt-7">{{ $orders->links() }}</div>
    @endif
@endsection
