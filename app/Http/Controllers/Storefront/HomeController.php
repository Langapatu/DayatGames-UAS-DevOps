<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Genre;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $baseQuery = Game::query()
            ->published()
            ->with(['developer', 'genres', 'images'])
            ->withCount('orderItems');

        return view('storefront.home', [
            'featuredGames' => (clone $baseQuery)
                ->where('is_featured', true)
                ->latest('release_date')
                ->take(5)
                ->get(),
            'discountedGames' => (clone $baseQuery)
                ->whereNotNull('discount_price')
                ->where('discount_percent', '>', 0)
                ->orderByDesc('discount_percent')
                ->take(8)
                ->get(),
            'popularGames' => (clone $baseQuery)
                ->orderByDesc('order_items_count')
                ->orderByDesc('is_featured')
                ->take(8)
                ->get(),
            'latestGames' => (clone $baseQuery)
                ->latest('release_date')
                ->take(8)
                ->get(),
            'genres' => Genre::query()
                ->whereHas('games', fn ($query) => $query->published())
                ->withCount(['games' => fn ($query) => $query->published()])
                ->orderByDesc('games_count')
                ->take(10)
                ->get(),
        ]);
    }
}
