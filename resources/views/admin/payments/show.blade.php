@extends('layouts.app')

@section('title', 'Payment '.$payment->order->order_code.' — Admin')

@section('content')
    @php
        $state = match ($payment->status) {
            'verified' => $payment->verified_by ? 'Diverifikasi admin' : 'Terdeteksi otomatis',
            'failed' => 'Gagal / dibatalkan',
            default => 'Menunggu pembayaran',
        };
    @endphp

    <header>
        <div>
            <p>Riwayat payment</p>
            <h1>{{ $payment->order->order_code }}</h1>
            <p>Detail transaksi simulasi bersifat read-only dan tidak memerlukan verifikasi manual.</p>
        </div>
        <a href="{{ route('admin.payments.index') }}">Kembali ke riwayat</a>
    </header>

    <div class="grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
        <section class="h-fit rounded-2xl border border-slate-800 bg-slate-900 p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 class="font-bold text-white">Data pembayaran</h2>
                <span class="rounded-full border border-cyan-500/25 bg-cyan-500/10 px-3 py-1 text-xs font-bold text-cyan-200">{{ $state }}</span>
            </div>
            <dl class="mt-4 space-y-4 text-sm">
                <div><dt class="text-slate-500">Customer</dt><dd class="text-slate-200">{{ $payment->order->user->name }} · {{ $payment->order->user->email }}</dd></div>
                <div><dt class="text-slate-500">Metode</dt><dd class="text-slate-200">{{ str_replace('_', ' ', strtoupper($payment->payment_method)) }}</dd></div>
                <div><dt class="text-slate-500">Jumlah</dt><dd class="text-xl font-bold text-cyan-300">Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</dd></div>
                <div><dt class="text-slate-500">Referensi simulasi / VA</dt><dd class="break-all font-mono text-slate-200">{{ $payment->payment_reference ?: $payment->virtual_account_number ?: '-' }}</dd></div>
                <div><dt class="text-slate-500">Terdeteksi pada</dt><dd class="text-slate-200">{{ $payment->paid_at?->format('d/m/Y H:i') ?: '-' }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="text-slate-200">{{ strtoupper($payment->status) }}</dd></div>
            </dl>

            @if($payment->status === 'verified')
                <p class="mt-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-3 text-sm leading-6 text-emerald-200">
                    {{ $payment->verified_by
                        ? 'Pembayaran legacy diverifikasi oleh '.$payment->verifier?->name.'.'
                        : 'Pembayaran terdeteksi otomatis oleh simulasi DayatGames.' }}
                </p>
            @elseif($payment->status === 'pending')
                <p class="mt-6 rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-sm leading-6 text-amber-200">Customer belum menjalankan simulasi pembayaran.</p>
            @else
                <p class="mt-6 rounded-xl border border-red-500/20 bg-red-500/10 p-3 text-sm leading-6 text-red-200">Payment gagal atau order telah dibatalkan.</p>
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
