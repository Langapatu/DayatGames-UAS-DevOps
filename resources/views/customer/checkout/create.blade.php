@extends('layouts.app')

@section('title', 'Checkout — DayatGames')

@section('content')
    <header class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Buat pesanan</p>
        <h1 class="text-4xl font-black text-white">Checkout</h1>
        <p class="mt-2 text-slate-400">Pilih metode pembayaran. Instruksi akan ditampilkan setelah pesanan dibuat. Pembayaran akan dideteksi otomatis.</p>
    </header>

    <div class="grid gap-8 lg:grid-cols-[1fr_400px]">
        <section>
            <h2 class="mb-4 text-xl font-bold text-white">Game dalam pesanan</h2>
            <div class="space-y-3">
                @foreach($cart->items as $item)
                    <article class="flex items-center gap-4 rounded-xl border border-slate-800 bg-slate-900 p-4">
                        <img src="{{ $item->game->coverUrl() }}" alt="Cover {{ $item->game->title }}" class="game-image-contain game-poster-thumbnail rounded-lg">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-white">{{ $item->game->title }}</h3>
                            <p class="text-sm text-slate-400">{{ $item->game->developer->name }}</p>
                        </div>
                        <strong class="text-cyan-300">Rp{{ number_format((float) $item->game->currentPrice(), 0, ',', '.') }}</strong>
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="h-fit space-y-4">
            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                <h2 class="font-bold text-white">Voucher</h2>
                @if($voucher)
                    <div class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3">
                        <div>
                            <strong class="text-emerald-200">{{ $voucher->code }}</strong>
                            <p class="text-xs text-emerald-300">Diskon {{ $voucher->discount_percent }}%</p>
                        </div>
                        <form method="POST" action="{{ route('checkout.voucher.remove') }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm font-semibold text-red-300">Hapus</button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('checkout.voucher.apply') }}" class="mt-4 flex gap-2">
                        @csrf
                        <label for="voucher-code" class="sr-only">Kode voucher</label>
                        <input id="voucher-code" name="voucher_code" value="{{ old('voucher_code') }}" placeholder="Kode voucher" maxlength="50" class="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                        <button class="rounded-lg bg-slate-700 px-4 py-2 font-semibold text-white">Gunakan</button>
                    </form>
                @endif
            </section>

            <form method="POST" action="{{ route('checkout.store') }}" class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                @csrf
                <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
                <h2 class="text-xl font-bold text-white">Metode pembayaran</h2>
                <fieldset class="mt-5 space-y-3">
                    <label data-payment-method class="flex cursor-pointer gap-3 rounded-xl border border-slate-700 p-3">
                        <input type="radio" name="payment_method" value="virtual_account" @checked(old('payment_method', 'virtual_account') === 'virtual_account')>
                        <span><strong class="block text-white">Virtual Account</strong><small class="text-slate-400">Nomor VA simulasi dibuat otomatis.</small></span>
                    </label>
                    <label data-payment-method class="flex cursor-pointer gap-3 rounded-xl border border-slate-700 p-3">
                        <input type="radio" name="payment_method" value="bank_transfer" @checked(old('payment_method') === 'bank_transfer')>
                        <span><strong class="block text-white">Transfer Bank</strong><small class="text-slate-400">Instruksi rekening demo muncul setelah order.</small></span>
                    </label>
                    <label data-payment-method class="flex cursor-pointer gap-3 rounded-xl border border-slate-700 p-3">
                        <input type="radio" name="payment_method" value="e_wallet" @checked(old('payment_method') === 'e_wallet')>
                        <span><strong class="block text-white">E-Wallet</strong><small class="text-slate-400">Instruksi akun demo muncul setelah order.</small></span>
                    </label>
                </fieldset>

                <dl class="mt-6 space-y-3 border-t border-slate-700 pt-5 text-sm">
                    <div class="flex justify-between text-slate-300"><dt>Subtotal</dt><dd>Rp{{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                    @if($voucher)
                        <div class="flex justify-between text-emerald-300"><dt>Diskon {{ $voucher->code }}</dt><dd>-Rp{{ number_format($voucherDiscount, 0, ',', '.') }}</dd></div>
                    @endif
                    <div class="flex justify-between text-base"><dt class="font-semibold text-white">Total</dt><dd class="text-xl font-black text-cyan-300">Rp{{ number_format($total, 0, ',', '.') }}</dd></div>
                </dl>

                <button class="mt-6 w-full rounded-xl bg-violet-600 px-4 py-3 font-bold text-white hover:bg-violet-500">Buat pesanan</button>
                <p class="mt-3 text-xs leading-5 text-amber-300">Simulasi akademik—tidak terhubung ke bank atau payment gateway nyata.</p>
            </form>
        </aside>
    </div>
@endsection
