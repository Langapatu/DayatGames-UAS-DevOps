<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CheckoutRequest;
use App\Services\CheckoutService;
use App\Services\VoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly VoucherService $vouchers,
    ) {}

    public function create(): View|RedirectResponse
    {
        $cart = auth()->user()->cart;
        $cart?->load(['items.game.developer']);

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Cart kosong dan tidak dapat di-checkout.');
        }

        $checkoutToken = session('checkout_token');
        if (! is_string($checkoutToken) || ! Str::isUuid($checkoutToken)) {
            $checkoutToken = (string) Str::uuid();
            session(['checkout_token' => $checkoutToken]);
        }

        $voucherCode = session('checkout_voucher_code');
        $voucher = $this->vouchers->findValid(is_string($voucherCode) ? $voucherCode : null);
        $subtotal = (float) $cart->items->sum(
            fn ($item) => (float) $item->game->currentPrice(),
        );
        $voucherDiscount = $voucher ? $this->vouchers->discount($subtotal, $voucher) : 0.0;
        $total = max(0, $subtotal - $voucherDiscount);

        return view('customer.checkout.create', compact(
            'cart',
            'checkoutToken',
            'voucher',
            'subtotal',
            'voucherDiscount',
            'total',
        ));
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $voucherCode = session('checkout_voucher_code');
        $order = $this->checkout->create(
            $request->user(),
            $request->validated('checkout_token'),
            $request->validated('payment_method'),
            is_string($voucherCode) ? $voucherCode : null,
        );

        session()->forget(['checkout_token', 'checkout_voucher_code']);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order berhasil dibuat. Ikuti instruksi pembayaran untuk melanjutkan.');
    }

    public function applyVoucher(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'voucher_code' => ['required', 'string', 'max:50'],
        ]);
        $voucher = $this->vouchers->findValid($validated['voucher_code']);

        if (! $voucher) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Kode voucher tidak ditemukan, nonaktif, atau sudah kedaluwarsa.',
            ]);
        }

        session(['checkout_voucher_code' => $voucher->code]);

        return redirect()->route('checkout.create')
            ->with('success', "Voucher {$voucher->code} berhasil digunakan.");
    }

    public function removeVoucher(): RedirectResponse
    {
        session()->forget('checkout_voucher_code');

        return redirect()->route('checkout.create')
            ->with('success', 'Voucher dihapus dari checkout.');
    }
}
