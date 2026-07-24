<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DayatGames')</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#070A12] text-slate-100 antialiased">
    <header class="sticky top-0 z-50 border-b border-slate-800/80 bg-slate-950/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" aria-label="DayatGames — kembali ke home" class="shrink-0">
                <img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt="DayatGames" width="56" height="56">
            </a>
            <nav aria-label="Navigasi utama" class="flex flex-wrap items-center justify-end gap-4 text-sm font-medium text-slate-300">
                <a href="{{ route('home') }}" class="hover:text-cyan-300">Home</a>
                <a href="{{ route('catalog.index') }}" class="hover:text-cyan-300">Semua Game</a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-cyan-300">Admin</a>
                        <a href="{{ route('admin.games.index') }}" class="hover:text-cyan-300">Games</a>
                        <a href="{{ route('admin.genres.index') }}" class="hover:text-cyan-300">Genres</a>
                        <a href="{{ route('admin.publishers.index') }}" class="hover:text-cyan-300">Publishers</a>
                        <a href="{{ route('admin.developers.index') }}" class="hover:text-cyan-300">Developers</a>
                        <a href="{{ route('admin.users.index') }}" class="hover:text-cyan-300">Customers</a>
                        <a href="{{ route('admin.orders.index') }}" class="hover:text-cyan-300">Orders</a>
                        <a href="{{ route('admin.payments.index') }}" class="hover:text-cyan-300">Payments</a>
                        <a href="{{ route('admin.reviews.index') }}" class="hover:text-cyan-300">Reviews</a>
                    @else
                        <a href="{{ route('wishlist.index') }}" class="hover:text-cyan-300">Wishlist</a>
                        <a href="{{ route('cart.index') }}" class="hover:text-cyan-300">Cart</a>
                        <a href="{{ route('orders.index') }}" class="hover:text-cyan-300">Orders</a>
                        <a href="{{ route('library.index') }}" class="hover:text-cyan-300">Library</a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="hover:text-cyan-300">Profil</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-700 px-3 py-2 hover:border-red-400 hover:text-red-300">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-cyan-300">Login</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-violet-600 px-3 py-2 font-semibold text-white hover:bg-violet-500">Registrasi</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if(session('success'))
            <div role="status" class="mb-5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div role="alert" class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-200">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div role="alert" class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-200">
                <p class="font-semibold">Periksa kembali input berikut:</p>
                <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-12 border-t border-slate-800 bg-slate-950">
        <div class="mx-auto max-w-7xl px-4 py-10 text-sm text-slate-400 sm:px-6 lg:px-8">
            <p class="font-semibold text-slate-200">DayatGames — Temukan. Beli. Mainkan.</p>
            <p class="mt-3 max-w-4xl leading-6">DayatGames merupakan aplikasi akademik untuk keperluan pembelajaran. Nama game, merek, dan aset terkait merupakan milik pemegang hak masing-masing. Harga yang ditampilkan merupakan data demonstrasi dan dapat berbeda dari harga toko resmi.</p>
        </div>
    </footer>
</body>
</html>
