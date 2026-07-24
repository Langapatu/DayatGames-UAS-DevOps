@extends('layouts.app')

@section('title', 'Login — DayatGames')

@section('content')
    <section aria-labelledby="login-title">
        <h1 id="login-title">Masuk ke DayatGames</h1>
        <p>Gunakan akun Anda untuk melanjutkan ke marketplace.</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                @error('email') <p role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
                @error('password') <p role="alert">{{ $message }}</p> @enderror
            </div>

            <label>
                <input name="remember" type="checkbox" value="1">
                Ingat saya
            </label>

            <button type="submit">Login</button>
        </form>

        <p>Belum punya akun? <a href="{{ route('register') }}">Buat akun customer</a>.</p>
    </section>
@endsection

