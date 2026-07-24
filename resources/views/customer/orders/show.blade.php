@extends('layouts.app')

@section('title', $order->order_code.' — DayatGames')

@section('content')
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Konfirmasi order</p><h1 class="text-4xl font-black text-white">{{ $order->order_code }}</h1><p class="mt-2 text-slate-400">{{ $order->ordered_at->format('d/m/Y H:i') }}</p></div>
        <span class="rounded-full border border-slate-700 bg-slate-900 px-4 py-2 font-semibold text-slate-200">{{ strtoupper($order->status) }}</span>
    </div>
    <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
        <section class="space-y-3">
            @foreach($order->items as $item)
                <article class="flex items-center gap-4 rounded-xl border border-slate-800 bg-slate-900 p-4">
                    @if($item->game)<img src="{{ $item->game->coverUrl() }}" alt="" class="game-image-contain game-poster-thumbnail rounded-lg">@endif
                    <div class="flex-1"><h2 class="font-semibold text-white">{{ $item->game_title }}</h2><p class="text-sm text-slate-500">Snapshot transaksi</p></div>
                    <strong class="text-cyan-300">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</strong>
                </article>
            @endforeach
        </section>
        <aside class="h-fit rounded-2xl border border-slate-800 bg-slate-900 p-6">
            <h2 class="text-xl font-bold text-white">Pembayaran</h2>
            <dl class="mt-5 space-y-4 text-sm">
                <div><dt class="text-slate-500">Metode</dt><dd class="text-slate-200">{{ str_replace('_', ' ', strtoupper($order->payment->payment_method)) }}</dd></div>
                @if($order->payment->virtual_account_number)<div><dt class="text-slate-500">Virtual Account simulasi</dt><dd class="font-mono text-lg text-cyan-300">{{ $order->payment->virtual_account_number }}</dd></div>@endif
                @if($order->payment->payment_reference)<div><dt class="text-slate-500">Referensi</dt><dd class="text-slate-200">{{ $order->payment->payment_reference }}</dd></div>@endif
                <div><dt class="text-slate-500">Status payment</dt><dd class="text-slate-200">{{ strtoupper($order->payment->status) }}</dd></div>
                <div><dt class="text-slate-500">Total</dt><dd class="text-xl font-bold text-white">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</dd></div>
            </dl>
            <p class="mt-5 rounded-lg bg-amber-500/10 p-3 text-xs leading-5 text-amber-200">Pembayaran ini adalah simulasi akademik. Library aktif setelah admin memverifikasi payment.</p>
        </aside>
    </div>
@endsection
