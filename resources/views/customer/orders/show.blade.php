@extends('layouts.app')

@section('title', $order->order_code.' — DayatGames')

@section('content')
    @php
        $proofSubmitted = $order->hasSubmittedProof();
        $canSubmit = $order->status === 'pending' && $order->payment->status === 'pending';
        $expired = $order->payment_due_at?->isPast() && !$proofSubmitted;
        $stage = $order->status === 'completed'
            ? 'Selesai'
            : ($order->status === 'cancelled'
                ? 'Dibatalkan'
                : ($proofSubmitted ? 'Menunggu verifikasi' : 'Menunggu pembayaran'));
    @endphp

    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Detail pesanan</p>
            <h1 class="text-4xl font-black text-white">{{ $order->order_code }}</h1>
            <p class="mt-2 text-slate-400">{{ $order->ordered_at->format('d/m/Y H:i') }}</p>
        </div>
        <span class="rounded-full border border-slate-700 bg-slate-900 px-4 py-2 font-semibold text-slate-200">{{ $stage }}</span>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_400px]">
        <div class="space-y-6">
            <section class="space-y-3">
                @foreach($order->items as $item)
                    <article class="flex items-center gap-4 rounded-xl border border-slate-800 bg-slate-900 p-4">
                        @if($item->game)
                            <img src="{{ $item->game->coverUrl() }}" alt="Cover {{ $item->game_title }}" class="game-image-contain game-poster-thumbnail rounded-lg">
                        @endif
                        <div class="min-w-0 flex-1">
                            <h2 class="font-semibold text-white">{{ $item->game_title }}</h2>
                            <p class="text-sm text-slate-500">Snapshot transaksi</p>
                        </div>
                        <strong class="text-cyan-300">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</strong>
                    </article>
                @endforeach
            </section>

            <section class="rounded-2xl border border-cyan-500/20 bg-slate-900/90 p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-cyan-300">Tata cara pembayaran</p>
                        <h2 class="mt-1 text-2xl font-black text-white">{{ $instructions['label'] }}</h2>
                    </div>
                    <span class="rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-200">SIMULASI</span>
                </div>

                <div class="mt-5 rounded-xl border border-slate-700 bg-slate-950/80 p-4">
                    <p class="text-xs text-slate-500">{{ $instructions['destination_label'] }}</p>
                    <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
                        <strong class="break-all font-mono text-xl text-cyan-300">{{ $instructions['destination'] }}</strong>
                        <button type="button" data-copy-payment="{{ $instructions['destination'] }}" class="rounded-lg border border-cyan-500/30 px-3 py-2 text-sm font-semibold text-cyan-200">Salin</button>
                    </div>
                    <p class="mt-2 text-sm text-slate-400">Atas nama {{ $instructions['account_name'] }}</p>
                </div>

                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-800/60 p-3">
                        <dt class="text-slate-500">Nominal tepat</dt>
                        <dd class="mt-1 font-bold text-white">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-800/60 p-3">
                        <dt class="text-slate-500">Batas pembayaran</dt>
                        <dd class="mt-1 font-bold text-white">{{ $order->payment_due_at?->format('d/m/Y H:i') ?: '-' }}</dd>
                    </div>
                </dl>

                <ol class="mt-5 space-y-3">
                    @foreach($instructions['steps'] as $step)
                        <li class="flex gap-3 text-sm leading-6 text-slate-300">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-violet-500/15 font-bold text-violet-200">{{ $loop->iteration }}</span>
                            <span>{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>

                <p class="mt-5 rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-sm font-semibold text-amber-200">
                    Ini hanya simulasi akademik. Tidak perlu mengirim uang sungguhan.
                </p>
            </section>
        </div>

        <aside class="h-fit space-y-4">
            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <h2 class="text-xl font-bold text-white">Ringkasan</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div><dt class="text-slate-500">Metode</dt><dd class="text-slate-200">{{ $instructions['label'] }}</dd></div>
                    <div><dt class="text-slate-500">Subtotal</dt><dd class="text-slate-200">Rp{{ number_format((float) $order->subtotal_amount, 0, ',', '.') }}</dd></div>
                    @if((float) $order->voucher_discount_amount > 0)
                        <div><dt class="text-slate-500">Voucher {{ $order->voucher_code }}</dt><dd class="text-emerald-300">-Rp{{ number_format((float) $order->voucher_discount_amount, 0, ',', '.') }}</dd></div>
                    @endif
                    <div><dt class="text-slate-500">Total</dt><dd class="text-xl font-bold text-white">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</dd></div>
                    <div><dt class="text-slate-500">Status payment</dt><dd class="text-slate-200">{{ $stage }}</dd></div>
                </dl>
            </section>

            @if($canSubmit && !$expired)
                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                    <h2 class="text-xl font-bold text-white">{{ $proofSubmitted ? 'Ganti bukti pembayaran' : 'Kirim bukti pembayaran' }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">Admin akan memeriksa bukti sebelum game masuk ke Library.</p>
                    <form method="POST" action="{{ route('orders.payment.submit', $order) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf
                        @if(in_array($order->payment->payment_method, ['bank_transfer', 'e_wallet'], true))
                            <label class="block">
                                <span class="mb-1 block text-sm text-slate-300">Nomor referensi transaksi</span>
                                <input name="payment_reference" maxlength="100" value="{{ old('payment_reference', $order->payment->payment_reference) }}" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white" required>
                            </label>
                        @endif
                        <label class="block">
                            <span class="mb-1 block text-sm text-slate-300">File bukti</span>
                            <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full text-sm text-slate-300" required>
                            <small class="mt-1 block text-slate-500">JPG, PNG, WebP, atau PDF. Maksimal 4 MB.</small>
                        </label>
                        <button class="w-full rounded-xl bg-violet-600 px-4 py-3 font-bold text-white hover:bg-violet-500">{{ $proofSubmitted ? 'Simpan bukti baru' : 'Kirim untuk verifikasi' }}</button>
                    </form>
                    @if($proofSubmitted)
                        <a href="{{ asset($order->payment->payment_proof) }}" target="_blank" rel="noopener" class="mt-4 inline-flex text-sm font-semibold text-cyan-300">Lihat bukti saat ini ↗</a>
                    @endif
                </section>
            @elseif($expired && $order->status === 'pending')
                <p class="rounded-2xl border border-red-500/20 bg-red-500/10 p-5 text-sm leading-6 text-red-200">Batas pembayaran telah lewat. Kirim bukti akan membatalkan pesanan ini, lalu Anda dapat membuat order baru.</p>
            @endif

            @if($order->status === 'pending' && !$proofSubmitted)
                <form method="POST" action="{{ route('orders.cancel', $order) }}" data-confirm="Batalkan pesanan ini?">
                    @csrf
                    <button class="w-full rounded-xl border border-red-500/40 px-4 py-3 font-semibold text-red-300 hover:bg-red-500/10">Batalkan pesanan</button>
                </form>
            @endif
        </aside>
    </div>
@endsection
