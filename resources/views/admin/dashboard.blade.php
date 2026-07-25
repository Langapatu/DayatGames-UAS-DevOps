@extends('layouts.app')

@section('title', 'Dashboard Admin — DayatGames')

@section('content')
    <section aria-labelledby="dashboard-title">
        <p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Control center</p>
        <h1 id="dashboard-title" class="text-4xl font-black text-white">Dashboard admin</h1>
        <dl class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['Game', $stats['games']],
                ['Customer', $stats['customers']],
                ['Order', $stats['orders']],
                ['Order pending', $stats['pending_orders']],
                ['Siap diverifikasi', $stats['payments_ready']],
                ['Menunggu customer', $stats['payments_waiting']],
                ['Payment verified', $stats['verified_payments']],
                ['Pendapatan', 'Rp'.number_format((float) $stats['revenue'], 0, ',', '.')],
            ] as [$label, $value])
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <dt class="text-sm text-slate-400">{{ $label }}</dt>
                    <dd class="mt-2 text-3xl font-black text-white">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    <div class="mt-10 grid gap-8 lg:grid-cols-[1fr_380px]">
        <section aria-labelledby="recent-orders-title">
            <div class="mb-4 flex justify-between"><h2 id="recent-orders-title" class="text-xl font-bold text-white">Order terbaru</h2><a href="{{ route('admin.orders.index') }}" class="text-sm text-cyan-300">Semua order →</a></div>
            <div class="overflow-x-auto rounded-xl border border-slate-800">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-900 text-slate-400"><tr><th class="p-4">Kode</th><th class="p-4">Customer</th><th class="p-4">Total</th><th class="p-4">Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($recentOrders as $order)
                            <tr><td class="p-4"><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-cyan-300">{{ $order->order_code }}</a></td><td class="p-4">{{ $order->user->name }}</td><td class="p-4">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</td><td class="p-4">{{ ucfirst($order->status) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada order.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section aria-labelledby="top-games-title">
            <h2 id="top-games-title" class="mb-4 text-xl font-bold text-white">Game terlaris</h2>
            <ol class="space-y-3">
                @foreach($topGames as $game)
                    <li class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-900 p-3">
                        <img src="{{ $game->coverUrl() }}" alt="" class="game-image-contain game-poster-thumbnail rounded">
                        <div class="min-w-0 flex-1"><p class="truncate font-semibold text-white">{{ $game->title }}</p><p class="text-xs text-slate-400">{{ $game->order_items_count }} terjual</p></div>
                        <span class="text-xs text-cyan-300">Rp{{ number_format((float) ($game->order_items_sum_subtotal ?? 0), 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
@endsection
