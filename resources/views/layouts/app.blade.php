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
@php
    $isAdminArea = request()->routeIs('admin.*') && auth()->check() && auth()->user()->isAdmin();
    $isAuthPage = request()->routeIs('login', 'register');
    $cartItemCount = auth()->check() && ! auth()->user()->isAdmin()
        ? (auth()->user()->cart()->withCount('items')->first()?->items_count ?? 0)
        : 0;
    $adminNavigation = [
        ['label' => 'Ringkasan', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => '⌂'],
        ['label' => 'Game', 'route' => 'admin.games.index', 'match' => 'admin.games.*', 'icon' => '▦'],
        ['label' => 'Genre', 'route' => 'admin.genres.index', 'match' => 'admin.genres.*', 'icon' => '◇'],
        ['label' => 'Voucher', 'route' => 'admin.vouchers.index', 'match' => 'admin.vouchers.*', 'icon' => '%'],
        ['label' => 'Customer', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => '◎'],
        ['label' => 'Order', 'route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'icon' => '▤'],
        ['label' => 'Payment', 'route' => 'admin.payments.index', 'match' => 'admin.payments.*', 'icon' => 'Rp'],
        ['label' => 'Review', 'route' => 'admin.reviews.index', 'match' => 'admin.reviews.*', 'icon' => '★'],
    ];
@endphp
<body class="min-h-screen bg-[#070A12] text-slate-100 antialiased {{ $isAdminArea ? 'admin-workspace' : '' }} {{ $isAuthPage ? 'auth-workspace' : '' }} {{ !$isAdminArea && !$isAuthPage ? 'storefront-workspace' : '' }}">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    @if(!$isAdminArea && !$isAuthPage)
        <div data-ambient-backdrop class="ambient-backdrop" aria-hidden="true">
            <span data-aurora-layer="one" class="ambient-aurora ambient-aurora-one"></span>
            <span data-aurora-layer="two" class="ambient-aurora ambient-aurora-two"></span>
            <span class="ambient-stars ambient-stars-near"></span>
            <span class="ambient-stars ambient-stars-far"></span>
        </div>
    @endif

    @if($isAdminArea)
        <aside id="admin-sidebar" class="admin-sidebar" aria-label="Navigasi admin">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand" aria-label="DayatGames Admin">
                <span class="brand-mark"><img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt=""></span>
                <span><strong>DayatGames</strong><small>Admin console</small></span>
            </a>
            <nav class="admin-navigation">
                <p class="admin-nav-label">Workspace</p>
                @foreach($adminNavigation as $item)
                    <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['match']) ? 'is-active' : '' }}">
                        <span class="admin-nav-icon" aria-hidden="true">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="admin-account">
                <span class="admin-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="min-w-0"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span>
            </div>
        </aside>
        <div class="admin-page">
            <header class="admin-topbar">
                <button type="button" data-admin-menu-toggle aria-controls="admin-sidebar" aria-expanded="false" class="admin-mobile-menu">☰ <span>Menu</span></button>
                <div class="admin-system-status">
                    <span class="admin-live-dot" aria-hidden="true"></span>
                    <span>Sistem operasional</span>
                </div>
                <div class="admin-top-actions">
                    <a href="{{ route('home') }}">Lihat toko ↗</a>
                    <a href="{{ route('profile.edit') }}">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Keluar</button>
                    </form>
                </div>
            </header>
    @else
        <header data-site-header class="site-header">
            <div class="site-header-inner">
                <a href="{{ route('home') }}" aria-label="DayatGames — kembali ke home" class="site-brand" data-interactive-brand>
                    <span class="brand-mark"><img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt=""></span>
                    <span data-brand-wordmark>Dayat<span>Games</span></span>
                </a>
                <button type="button" data-menu-toggle aria-controls="main-navigation" aria-expanded="false" class="mobile-menu-button">
                    <span aria-hidden="true">☰</span> Menu
                </button>
                <nav id="main-navigation" data-site-navigation data-open="false" aria-label="Navigasi utama" class="site-navigation">
                    <div class="site-nav-primary">
                        <a data-nav-link href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Beranda</a>
                        <a data-nav-link href="{{ route('catalog.index') }}" class="{{ request()->routeIs('catalog.*') ? 'is-active' : '' }}">Jelajahi Game</a>
                    </div>
                    <div class="site-nav-account">
                        @auth
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="nav-admin-cta">Buka Admin</a>
                            @else
                                <a data-nav-link href="{{ route('wishlist.index') }}" class="{{ request()->routeIs('wishlist.*') ? 'is-active' : '' }}">Wishlist</a>
                                <a data-nav-link href="{{ route('library.index') }}" class="{{ request()->routeIs('library.*') ? 'is-active' : '' }}">Library</a>
                                <a data-nav-link href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'is-active' : '' }}">Lacak Pesanan</a>
                                <a data-nav-link href="{{ route('cart.index') }}" class="nav-cart {{ request()->routeIs('cart.*') ? 'is-active' : '' }}" aria-label="Cart, {{ $cartItemCount }} game">
                                    <span>Cart</span>
                                    <span class="nav-cart-count" aria-hidden="true">{{ $cartItemCount }}</span>
                                </a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="nav-profile" aria-label="Buka profil">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-logout">Keluar</button>
                            </form>
                        @else
                            <a data-nav-link href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'is-active' : '' }}">Masuk</a>
                            <a data-interactive-button href="{{ route('register') }}" class="nav-register">Buat akun</a>
                        @endauth
                    </div>
                </nav>
            </div>
        </header>
    @endif

    <main id="main-content"
          @if(!$isAdminArea && !$isAuthPage) data-page-content @endif
          class="{{ $isAdminArea ? 'admin-main' : ($isAuthPage ? 'auth-main' : 'site-main') }}">
        @if(session('success'))
            <div data-flash role="status" class="mb-5 flex items-start justify-between gap-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                <span>{{ session('success') }}</span><button type="button" data-flash-close aria-label="Tutup pesan">×</button>
            </div>
        @endif
        @if(session('error'))
            <div data-flash role="alert" class="mb-5 flex items-start justify-between gap-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-200">
                <span>{{ session('error') }}</span><button type="button" data-flash-close aria-label="Tutup pesan">×</button>
            </div>
        @endif
        @if($errors->any())
            <div role="alert" class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-200">
                <p class="font-semibold">Periksa kembali input berikut:</p>
                <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
    </main>

    @if($isAdminArea)
        </div>
    @elseif(!$isAuthPage)
        <footer class="site-footer">
            <div class="site-footer-grid">
                <section class="site-footer-brand" aria-labelledby="footer-brand-title">
                    <a href="{{ route('home') }}" class="site-footer-logo">
                        <span class="brand-mark"><img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt=""></span>
                        <strong id="footer-brand-title">Dayat<span>Games</span></strong>
                    </a>
                    <p>DayatGames — Temukan. Beli. Mainkan.</p>
                    <p>Marketplace game digital modern untuk menjelajah katalog, membuat pesanan, dan mengelola library dalam satu tempat.</p>
                </section>

                <nav aria-labelledby="footer-shop-title">
                    <h2 id="footer-shop-title">Shop</h2>
                    <a href="{{ route('home') }}">Beranda</a>
                    <a href="{{ route('catalog.index') }}">Jelajahi Game</a>
                    @auth
                        @if(!auth()->user()->isAdmin())
                            <a href="{{ route('wishlist.index') }}">Wishlist</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}">Wishlist</a>
                    @endauth
                </nav>

                <nav aria-labelledby="footer-customer-title">
                    <h2 id="footer-customer-title">Customer</h2>
                    @auth
                        @if(!auth()->user()->isAdmin())
                            <a href="{{ route('cart.index') }}">Cart</a>
                            <a href="{{ route('orders.index') }}">Lacak Pesanan</a>
                            <a href="{{ route('library.index') }}">Library</a>
                            <a href="{{ route('profile.edit') }}">Profil</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
                            <a href="{{ route('profile.edit') }}">Profil</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}">Masuk</a>
                        <a href="{{ route('register') }}">Buat akun</a>
                    @endauth
                </nav>

                <section aria-labelledby="footer-payment-title">
                    <h2 id="footer-payment-title">Payment</h2>
                    <ul class="site-footer-payments">
                        <li><span aria-hidden="true">VA</span> Virtual Account</li>
                        <li><span aria-hidden="true">BT</span> Transfer Bank</li>
                        <li><span aria-hidden="true">EW</span> E-Wallet</li>
                    </ul>
                </section>
            </div>

            <div class="site-footer-bottom">
                <p>© {{ now()->year }} DayatGames. Dibuat untuk pembelajaran.</p>
                <p><strong>Simulasi akademik:</strong> tidak ada transaksi uang nyata. Nama, merek, dan aset game merupakan milik pemegang hak masing-masing.</p>
            </div>
        </footer>
    @endif
</body>
</html>
