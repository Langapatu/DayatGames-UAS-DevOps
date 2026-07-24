@extends('layouts.app')

@section('title', 'Registrasi — DayatGames')

@section('content')
    <section aria-labelledby="register-title">
        <h1 id="register-title">Buat akun DayatGames</h1>
        <p>Akun baru otomatis memiliki role customer.</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <label for="name">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name">
                @error('name') <p role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email') <p role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password">
                @error('password') <p role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation">Konfirmasi password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            </div>

            <button type="submit">Daftar</button>
        </form>

        <p>Sudah punya akun? <a href="{{ route('login') }}">Login</a>.</p>
    </section>
@endsection

