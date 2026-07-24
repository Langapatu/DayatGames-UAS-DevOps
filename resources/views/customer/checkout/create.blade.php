@extends('layouts.app')

@section('title', 'Checkout — DayatGames')

@section('content')
    <header class="mb-8"><p class="text-sm font-semibold uppercase tracking-widest text-violet-300">Transaksi aman</p><h1 class="text-4xl font-black text-white">Checkout</h1></header>

    <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
        <section>
            <h2 class="mb-4 text-xl font-bold text-white">Game dalam order</h2>
            <div class="space-y-3">
                @foreach($cart->items as $item)
                    <article class="flex items-center gap-4 rounded-xl border border-slate-800 bg-slate-900 p-4">
                        <img src="{{ asset($item->game->cover_image) }}" alt="" class="game-image-contain h-20 w-28 rounded-lg">
                        <div class="flex-1"><h3 class="font-semibold text-white">{{ $item->game->title }}</h3><p class="text-sm text-slate-400">{{ $item->game->developer->name }}</p></div>
                        <strong class="text-cyan-300">Rp{{ number_format((float) $item->game->currentPrice(), 0, ',', '.') }}</strong>
                    </article>
                @endforeach
            </div>
        </section>

        <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="h-fit rounded-2xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            <h2 class="text-xl font-bold text-white">Metode pembayaran</h2>
            <fieldset class="mt-5 space-y-3">
                <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-700 p-3"><input type="radio" name="payment_method" value="virtual_account" @checked(old('payment_method', 'virtual_account') === 'virtual_account')><span><strong class="block text-white">Virtual Account</strong><small class="text-slate-400">Nomor VA simulasi dibuat otomatis.</small></span></label>
                <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-700 p-3"><input type="radio" name="payment_method" value="bank_transfer" @checked(old('payment_method') === 'bank_transfer')><span><strong class="block text-white">Transfer Bank</strong><small class="text-slate-400">Unggah bukti transfer demo.</small></span></label>
                <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-700 p-3"><input type="radio" name="payment_method" value="e_wallet" @checked(old('payment_method') === 'e_wallet')><span><strong class="block text-white">E-Wallet</strong><small class="text-slate-400">Masukkan referensi dan bukti demo.</small></span></label>
            </fieldset>
            <label class="mt-5 block"><span class="mb-1 block text-sm text-slate-300">Referensi pembayaran (opsional untuk VA)</span><input name="payment_reference" value="{{ old('payment_reference') }}" maxlength="100" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white"></label>
            <label class="mt-4 block"><span class="mb-1 block text-sm text-slate-300">Bukti pembayaran</span><input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full text-sm text-slate-300"><small class="text-slate-500">Wajib untuk transfer bank/e-wallet, maksimal 4 MB.</small></label>
            <div class="mt-6 flex justify-between border-t border-slate-700 pt-5"><span class="text-slate-300">Total server</span><strong class="text-xl text-cyan-300">Rp{{ number_format((float) $cart->items->sum(fn($item) => (float) $item->game->currentPrice()), 0, ',', '.') }}</strong></div>
            <button class="mt-6 w-full rounded-xl bg-violet-600 px-4 py-3 font-bold text-white hover:bg-violet-500">Buat order</button>
            <p class="mt-3 text-xs leading-5 text-amber-300">Simulasi akademik—tidak terhubung ke bank atau payment gateway nyata.</p>
        </form>
    </div>
@endsection
