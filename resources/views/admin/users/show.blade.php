@extends('layouts.app')
@section('title', $user->name.' — Admin DayatGames')
@section('content')
    <h1 class="text-3xl font-black text-white">{{ $user->name }}</h1><p class="mt-2 text-slate-400">{{ $user->email }} · {{ $user->phone ?: 'Telepon belum diisi' }}</p>
    <div class="mt-8 grid gap-8 lg:grid-cols-2"><section><h2 class="mb-4 text-xl font-bold text-white">Order</h2><div class="space-y-3">@forelse($user->orders as $order)<a href="{{ route('admin.orders.show', $order) }}" class="block rounded-xl border border-slate-800 bg-slate-900 p-4"><strong class="text-white">{{ $order->order_code }}</strong><span class="float-right text-slate-300">{{ $order->status }}</span></a>@empty<p class="text-slate-400">Belum ada order.</p>@endforelse</div></section><section><h2 class="mb-4 text-xl font-bold text-white">Library</h2><div class="space-y-3">@forelse($user->libraries as $library)<div class="rounded-xl border border-slate-800 bg-slate-900 p-4 text-white">{{ $library->game->title }}</div>@empty<p class="text-slate-400">Belum ada game.</p>@endforelse</div></section></div>
@endsection
