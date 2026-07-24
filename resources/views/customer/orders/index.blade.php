@extends('layouts.app')

@section('title', 'Riwayat Order — DayatGames')

@section('content')
    <header class="mb-8"><p class="text-sm font-semibold uppercase tracking-widest text-cyan-300">Transaksi saya</p><h1 class="text-4xl font-black text-white">Riwayat order</h1></header>
    @if($orders->isEmpty())
        <x-empty-state title="Belum ada order" message="Order yang Anda buat akan tampil di sini."><a href="{{ route('catalog.index') }}" class="mt-5 inline-block text-cyan-300">Jelajahi game</a></x-empty-state>
    @else
        <div class="overflow-x-auto rounded-2xl border border-slate-800">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-900 text-slate-400"><tr><th class="p-4">Kode</th><th class="p-4">Tanggal</th><th class="p-4">Item</th><th class="p-4">Total</th><th class="p-4">Status</th><th class="p-4"></th></tr></thead>
                <tbody class="divide-y divide-slate-800">@foreach($orders as $order)<tr><td class="p-4 font-semibold text-white">{{ $order->order_code }}</td><td class="p-4 text-slate-300">{{ $order->ordered_at->format('d/m/Y H:i') }}</td><td class="p-4">{{ $order->items_count }}</td><td class="p-4">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</td><td class="p-4"><span class="rounded-full bg-slate-800 px-2 py-1">{{ $order->status }}</span></td><td class="p-4"><a href="{{ route('orders.show', $order) }}" class="text-cyan-300">Detail</a></td></tr>@endforeach</tbody>
            </table>
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
@endsection
