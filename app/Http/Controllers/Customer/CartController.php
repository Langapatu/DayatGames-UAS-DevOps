<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->user()->cart()->firstOrCreate();
        $cart->load(['items.game.developer']);

        return view('customer.cart.index', compact('cart'));
    }

    public function store(Request $request, Game $game): RedirectResponse
    {
        abort_unless($game->status === 'published', 404);

        if ($request->user()->libraries()->where('game_id', $game->id)->exists()) {
            return back()->with('error', 'Game ini sudah ada di library Anda.');
        }

        $cart = $request->user()->cart()->firstOrCreate();
        $item = $cart->items()->firstOrCreate(
            ['game_id' => $game->id],
            ['price' => $game->currentPrice()],
        );

        $message = $item->wasRecentlyCreated
            ? 'Game ditambahkan ke cart.'
            : 'Game sudah ada di cart.';

        return back()->with('success', $message);
    }

    public function destroy(Request $request, Game $game): RedirectResponse
    {
        $request->user()->cart?->items()->where('game_id', $game->id)->delete();

        return back()->with('success', 'Game dihapus dari cart.');
    }
}
