@extends('layouts.app')

@section('title', 'Daftar — DayatGames')

@section('content')
    <section class="auth-shell auth-shell-register" aria-labelledby="register-title">
        <div class="auth-story">
            <a href="{{ route('home') }}" class="auth-brand">
                <span class="brand-mark"><img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt=""></span>
                <span>Dayat<span>Games</span></span>
            </a>
            <div class="auth-story-copy">
                <span class="auth-kicker">Mulai koleksimu</span>
                <h2>Satu akun untuk semua game pilihanmu.</h2>
                <p>Buat profil customer dan nikmati pengalaman belanja game yang lebih rapi.</p>
                <ul>
                    <li><span>✓</span> Wishlist pribadi</li>
                    <li><span>✓</span> Riwayat order terorganisir</li>
                    <li><span>✓</span> Akses game dari library</li>
                </ul>
            </div>
            <p class="auth-story-foot">Akun baru otomatis terdaftar sebagai customer.</p>
        </div>

        <div class="auth-panel">
            <div class="auth-card">
                <div class="auth-heading">
                    <span class="auth-mobile-logo"><img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt=""></span>
                    <p>Bergabung dengan DayatGames</p>
                    <h1 id="register-title">Buat akun baru</h1>
                    <span>Hanya perlu beberapa data untuk mulai menjelajah.</span>
                </div>

                <form method="POST" action="{{ route('register') }}" class="auth-form">
                    @csrf
                    <div class="field-group">
                        <label for="name">Nama lengkap</label>
                        <div class="input-with-icon">
                            <span aria-hidden="true">A</span>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Nama lengkap" required autofocus autocomplete="name">
                        </div>
                        @error('name') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="field-group">
                        <label for="email">Alamat email</label>
                        <div class="input-with-icon">
                            <span aria-hidden="true">@</span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" required autocomplete="email">
                        </div>
                        @error('email') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-form-grid auth-password-grid">
                        <div class="field-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <span aria-hidden="true">●</span>
                                <input id="password" name="password" type="password" placeholder="Min. 8 karakter" required autocomplete="new-password">
                                <button type="button" data-password-toggle aria-label="Tampilkan password">Lihat</button>
                            </div>
                            @error('password') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                        </div>

                        <div class="field-group">
                            <label for="password_confirmation">Ulangi password</label>
                            <div class="input-with-icon">
                                <span aria-hidden="true">●</span>
                                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Konfirmasi" required autocomplete="new-password">
                                <button type="button" data-password-toggle aria-label="Tampilkan password">Lihat</button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit">Buat akun <span aria-hidden="true">→</span></button>
                </form>

                <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
                <p class="auth-secure"><span aria-hidden="true">◆</span> Dengan mendaftar, Anda menyetujui penggunaan data untuk layanan aplikasi.</p>
            </div>
        </div>
    </section>
@endsection
