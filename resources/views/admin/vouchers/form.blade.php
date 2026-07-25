@extends('layouts.app')

@section('title', ($voucher->exists ? 'Edit' : 'Tambah').' Voucher — Admin DayatGames')

@section('content')
    <header>
        <div>
            <p>Promosi checkout</p>
            <h1>{{ $voucher->exists ? 'Edit voucher' : 'Tambah voucher' }}</h1>
            <p>Kode akan disimpan dalam huruf besar dan divalidasi kembali saat order dibuat.</p>
        </div>
    </header>

    <form method="POST" action="{{ $voucher->exists ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}">
        @csrf
        @if($voucher->exists)
            @method('PUT')
        @endif

        <div>
            <div>
                <label for="code">Kode voucher</label>
                <input id="code" name="code" type="text" maxlength="50" value="{{ old('code', $voucher->code) }}" placeholder="HEMAT20" required>
                @error('code') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="discount_percent">Diskon (%)</label>
                <input id="discount_percent" name="discount_percent" type="number" min="1" max="100" value="{{ old('discount_percent', $voucher->discount_percent) }}" required>
                @error('discount_percent') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="expires_at">Berlaku sampai</label>
                <input id="expires_at" name="expires_at" type="date" value="{{ old('expires_at', $voucher->expires_at?->format('Y-m-d')) }}" required>
                @error('expires_at') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <label>
                <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $voucher->exists ? $voucher->is_active : true))>
                Voucher aktif dan dapat digunakan
            </label>
        </div>

        <button type="submit">{{ $voucher->exists ? 'Simpan perubahan' : 'Buat voucher' }}</button>
        <a href="{{ route('admin.vouchers.index') }}">Batal</a>
    </form>
@endsection
