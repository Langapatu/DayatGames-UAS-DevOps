@extends('layouts.app')

@section('title', 'Payment '.$payment->order->order_code.' — Admin')

@section('content')
    <header>
        <div>
            <p>Verifikasi payment</p>
            <h1>{{ $payment->order->order_code }}</h1>
            <p>Periksa customer, nominal, bukti, dan setiap game sebelum verifikasi.</p>
        </div>
        <a href="{{ route('admin.payments.index') }}">Kembali ke antrean</a>
    </header>

    <div class="grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
        <section class="h-fit rounded-2xl border border-slate-800 bg-slate-900 p-5">
            <h2 class="font-bold text-white">Data pembayaran</h2>
            <dl class="mt-4 space-y-4 text-sm">
                <div><dt class="text-slate-500">Customer</dt><dd class="text-slate-200">{{ $payment->order->user->name }} · {{ $payment->order->user->email }}</dd></div>
                <div><dt class="text-slate-500">Metode</dt><dd class="text-slate-200">{{ str_replace('_', ' ', strtoupper($payment->payment_method)) }}</dd></div>
                <div><dt class="text-slate-500">Jumlah</dt><dd class="text-xl font-bold text-cyan-300">Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</dd></div>
                <div><dt class="text-slate-500">Referensi / VA</dt><dd class="break-all font-mono text-slate-200">{{ $payment->payment_reference ?: $payment->virtual_account_number ?: '-' }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="text-slate-200">{{ strtoupper($payment->status) }}</dd></div>
            </dl>

            @if($payment->payment_proof)
                <a href="{{ asset($payment->payment_proof) }}" target="_blank" rel="noopener" class="mt-5 inline-flex rounded-xl bg-cyan-500/10 px-4 py-3 font-semibold text-cyan-200">Buka bukti pembayaran ↗</a>
            @else
                <p class="mt-5 rounded-xl bg-amber-500/10 p-3 text-sm text-amber-200">Customer belum mengirim bukti. Payment ini belum dapat diverifikasi.</p>
            @endif

            @if($payment->status === 'pending')
                <div class="mt-6 flex flex-wrap gap-3">
                    @if($payment->payment_proof)
                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                            @csrf
                            <button class="rounded-lg bg-emerald-600 px-4 py-2 font-bold text-white">Verify payment</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                        @csrf
                        <button class="rounded-lg bg-red-600 px-4 py-2 font-bold text-white">Reject</button>
                    </form>
                </div>
            @elseif($payment->status === 'verified')
                <p class="mt-6 rounded-lg bg-emerald-500/10 p-3 text-emerald-300">Verified oleh {{ $payment->verifier?->name }} pada {{ $payment->verified_at?->format('d/m/Y H:i') }}.</p>
            @endif
        </section>

        <section>
            <h2 class="text-xl font-bold text-white">Game dalam order</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach($payment->order->items as $item)
                    <article class="flex gap-4 rounded-2xl border border-slate-800 bg-slate-900 p-4">
                        @if($item->game)
                            <img src="{{ $item->game->coverUrl() }}" alt="Cover {{ $item->game_title }}" class="h-32 w-24 shrink-0 rounded-xl border border-slate-700 bg-slate-950 object-contain">
                        @endif
                        <div class="min-w-0">
                            <h3 class="font-bold text-white">{{ $item->game_title }}</h3>
                            <p class="mt-2 text-xs text-slate-500">Harga snapshot</p>
                            <p class="mt-1 font-semibold text-cyan-300">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
@endsection
