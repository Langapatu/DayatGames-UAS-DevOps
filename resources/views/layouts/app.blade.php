<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DayatGames')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header>
        <a href="{{ route('home') }}" aria-label="DayatGames — kembali ke home">
            <img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt="DayatGames" width="56" height="56">
        </a>
        <nav aria-label="Navigasi utama">
            <a href="{{ route('home') }}">Home</a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
                <a href="{{ route('profile.edit') }}">Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Registrasi</a>
            @endauth
        </nav>
    </header>

    <main>
        @if(session('success'))
            <div role="status">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>

