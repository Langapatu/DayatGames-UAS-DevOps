@extends('layouts.app')

@section('title', 'Library Saya — DayatGames')

@section('content')
    <header class="mb-8"><p class="text-sm font-semibold uppercase tracking-widest text-emerald-300">Koleksi dimiliki</p><h1 class="text-4xl font-black text-white">Library saya</h1></header>
    @if($libraries->isEmpty())
        <x-empty-state title="Library masih kosong" message="Game masuk ke library setelah pembayaran diverifikasi admin."><a href="{{ route('orders.index') }}" class="mt-5 inline-block text-cyan-300">Lihat order</a></x-empty-state>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($libraries as $library)
                <article class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                    <img src="{{ $library->game->coverUrl() }}" alt="Cover {{ $library->game->title }}" class="game-image-contain aspect-[4/5] w-full">
                    <div class="p-5"><h2 class="text-xl font-bold text-white">{{ $library->game->title }}</h2><p class="text-sm text-slate-400">{{ $library->game->developer->name }}</p><p class="mt-3 text-xs text-slate-500">Dibeli {{ $library->purchased_at->format('d/m/Y') }} · {{ $library->order->order_code }}</p><a href="{{ route('reviews.create', $library->game) }}" class="mt-5 inline-block rounded-lg border border-violet-500/60 px-3 py-2 text-sm font-semibold text-violet-200">Tulis / edit review</a></div>
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $libraries->links() }}</div>
    @endif
@endsection
