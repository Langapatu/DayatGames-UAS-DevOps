@extends('layouts.app')

@section('title', $order->order_code.' — DayatGames')

@section('content')
    @php
        $canPay = $order->status === 'pending' && $order->payment->status === 'pending';
        $expired = $canPay && $order->payment_due_at?->isPast();
        $stage = $order->trackingStage();
    @endphp

    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Detail pesanan</p>
            <h1 class="text-4xl font-black text-white">{{ $order->order_code }}</h1>
            <p class="mt-2 text-slate-400">{{ $order->ordered_at->format('d/m/Y H:i') }}</p>
        </div>
        <span class="rounded-full border border-slate-700 bg-slate-900 px-4 py-2 font-semibold text-slate-200">{{ $stage }}</span>
    </div>

    <ol data-order-timeline class="mb-8 grid gap-3 rounded-2xl border border-slate-800 bg-slate-900/80 p-4 sm:grid-cols-4">
        @foreach($order->trackingSteps() as $step)
            <li class="rounded-xl border p-3 text-sm {{ $step['state'] === 'completed' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' : ($step['state'] === 'current' ? 'border-cyan-500/40 bg-cyan-500/10 text-cyan-100' : ($step['state'] === 'failed' ? 'border-red-500/30 bg-red-500/10 text-red-200' : 'border-slate-800 bg-slate-950/50 text-slate-500')) }}">
                <span class="mb-2 block text-xs font-black uppercase tracking-widest">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="font-semibold">{{ $step['label'] }}</span>
            </li>
        @endforeach
    </ol>

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

            @if($canPay && !$expired)
                <section class="overflow-hidden rounded-2xl border border-cyan-500/25 bg-gradient-to-br from-cyan-500/10 via-slate-900 to-violet-500/10 p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex size-11 items-center justify-center rounded-2xl border border-cyan-400/30 bg-cyan-400/10 text-xl text-cyan-200">✓</span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-cyan-300">Deteksi otomatis</p>
                            <h2 class="mt-1 text-xl font-black text-white">Siap melakukan pembayaran?</h2>
                        </div>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-slate-300">
                        Tekan tombol di bawah untuk menjalankan simulasi. Sistem akan langsung mendeteksi pembayaran dan menambahkan game ke Library.
                    </p>
                    <p class="mt-3 rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-xs font-semibold leading-5 text-amber-200">
                        Simulasi akademik—tidak ada uang sungguhan yang dikirim.
                    </p>
                    <form method="POST" action="{{ route('orders.payment.submit', $order) }}" data-auto-payment class="mt-5">
                        @csrf
                        <button data-auto-payment-button class="relative flex w-full items-center justify-center gap-3 overflow-hidden rounded-xl bg-gradient-to-r from-cyan-500 to-violet-600 px-4 py-3 font-black text-white shadow-lg shadow-cyan-950/30 transition hover:-translate-y-0.5 hover:shadow-cyan-900/40 disabled:cursor-wait disabled:opacity-80">
                            <span data-auto-payment-label>Bayar sekarang</span>
                            <span data-auto-payment-progress class="hidden items-center gap-2">
                                <span class="size-4 animate-spin rounded-full border-2 border-white/35 border-t-white" aria-hidden="true"></span>
                                Mendeteksi pembayaran...
                            </span>
                        </button>
                    </form>
                </section>
            @elseif($expired)
                <p class="rounded-2xl border border-red-500/20 bg-red-500/10 p-5 text-sm leading-6 text-red-200">Batas pembayaran telah lewat. Pesanan ini tidak dapat dibayar dan Anda perlu membuat pesanan baru.</p>
            @elseif($order->status === 'completed' && $order->payment->status === 'verified')
                <section class="rounded-2xl border border-emerald-500/25 bg-emerald-500/10 p-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-emerald-300">Terdeteksi otomatis</p>
                    <h2 class="mt-2 text-xl font-black text-white">Pembayaran selesai</h2>
                    <p class="mt-2 text-sm leading-6 text-emerald-100/80">Game pada pesanan ini sudah tersedia di Library Anda.</p>
                    <a href="{{ route('library.index') }}" class="mt-5 inline-flex rounded-xl bg-emerald-500 px-4 py-3 font-bold text-slate-950 hover:bg-emerald-400">Buka Library</a>
                </section>
            @endif

            @if($canPay)
                <form method="POST" action="{{ route('orders.cancel', $order) }}" data-confirm="Batalkan pesanan ini?">
                    @csrf
                    <button class="w-full rounded-xl border border-red-500/40 px-4 py-3 font-semibold text-red-300 hover:bg-red-500/10">Batalkan pesanan</button>
                </form>
            @endif
        </aside>
    </div>
@endsection
