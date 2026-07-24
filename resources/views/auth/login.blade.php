@extends('layouts.app')

@section('title', 'Masuk — DayatGames')

@section('content')
    <section class="auth-shell" aria-labelledby="login-title">
        <div class="auth-story">
            <a href="{{ route('home') }}" class="auth-brand">
                <span class="brand-mark"><img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt=""></span>
                <span>Dayat<span>Games</span></span>
            </a>
            <div class="auth-story-copy">
                <span class="auth-kicker">Marketplace game digital</span>
                <h2>Kembali ke dunia game favoritmu.</h2>
                <p>Kelola wishlist, transaksi, dan seluruh koleksi game dalam satu akun.</p>
                <ul>
                    <li><span>✓</span> Checkout sederhana dan transparan</li>
                    <li><span>✓</span> Library tersimpan di akunmu</li>
                    <li><span>✓</span> Harga dan promo mudah dibandingkan</li>
                </ul>
            </div>
            <p class="auth-story-foot">Temukan. Beli. Mainkan.</p>
        </div>

        <div class="auth-panel">
            <div class="auth-card">
                <div class="auth-heading">
                    <span class="auth-mobile-logo"><img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt=""></span>
                    <p>Selamat datang kembali</p>
                    <h1 id="login-title">Masuk ke akun</h1>
                    <span>Lanjutkan perjalanan gaming-mu di DayatGames.</span>
                </div>

                <form method="POST" action="{{ route('login') }}" class="auth-form">
                    @csrf
                    <div class="field-group">
                        <label for="email">Alamat email</label>
                        <div class="input-with-icon">
                            <span aria-hidden="true">@</span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus autocomplete="email">
                        </div>
                        @error('email') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="field-group">
                        <div class="field-label-row"><label for="password">Password</label><span>Minimal 8 karakter</span></div>
                        <div class="input-with-icon">
                            <span aria-hidden="true">●</span>
                            <input id="password" name="password" type="password" placeholder="Masukkan password" required autocomplete="current-password">
                            <button type="button" data-password-toggle aria-label="Tampilkan password">Lihat</button>
                        </div>
                        @error('password') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <label class="auth-check">
                        <input name="remember" type="checkbox" value="1">
                        <span>Ingat saya di perangkat ini</span>
                    </label>

                    <button type="submit" class="auth-submit">Masuk sekarang <span aria-hidden="true">→</span></button>
                </form>

                <p class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar gratis</a></p>
                <p class="auth-secure"><span aria-hidden="true">◆</span> Data login diamankan dengan proteksi aplikasi.</p>
            </div>
        </div>
    </section>
@endsection
