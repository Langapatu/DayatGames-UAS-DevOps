<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeveloperController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\LibraryController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/games', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/games/{game}', [CatalogController::class, 'show'])->name('catalog.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{game}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{game}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{game}', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/{game}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout/voucher', [CheckoutController::class, 'applyVoucher'])->name('checkout.voucher.apply');
    Route::delete('/checkout/voucher', [CheckoutController::class, 'removeVoucher'])->name('checkout.voucher.remove');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/library/{game}/review', [CustomerReviewController::class, 'create'])->name('reviews.create');
    Route::post('/library/{game}/review', [CustomerReviewController::class, 'store'])->name('reviews.store');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::resource('genres', GenreController::class)->except('show');
        Route::resource('vouchers', VoucherController::class)->except('show');
        Route::resource('publishers', PublisherController::class)->except('show');
        Route::resource('developers', DeveloperController::class)->except('show');
        Route::resource('games', GameController::class);
        Route::resource('users', AdminUserController::class)->only(['index', 'show']);
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
        Route::resource('payments', AdminPaymentController::class)->only(['index', 'show']);
        Route::post('payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
        Route::post('payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');
        Route::resource('reviews', AdminReviewController::class)->only(['index', 'update']);
    });
