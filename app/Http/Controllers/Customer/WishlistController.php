<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $wishlists = $request->user()
            ->wishlists()
            ->with(['game.developer', 'game.genres'])
            ->latest()
            ->paginate(12);

        return view('customer.wishlist.index', compact('wishlists'));
    }

    public function store(Request $request, Game $game): RedirectResponse
    {
        abort_unless($game->status === 'published', 404);

        $wishlist = $request->user()->wishlists()->firstOrCreate(['game_id' => $game->id]);
        $message = $wishlist->wasRecentlyCreated
            ? 'Game ditambahkan ke wishlist.'
            : 'Game sudah ada di wishlist.';

        return back()->with('success', $message);
    }

    public function destroy(Request $request, Game $game): RedirectResponse
    {
        $request->user()->wishlists()->where('game_id', $game->id)->delete();

        return back()->with('success', 'Game dihapus dari wishlist.');
    }
}
