<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'genre' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'sort' => ['nullable', 'in:latest,oldest,price_asc,price_desc,discount'],
        ]);

        // `+ 0` keeps comparisons numeric on both MySQL and the SQLite test database.
        $priceExpression = '(COALESCE(discount_price, original_price) + 0)';
        $games = Game::query()
            ->published()
            ->with(['developer', 'genres'])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = (string) $request->string('search');
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('short_description', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('genre'), fn (Builder $query) => $query
                ->whereHas('genres', fn (Builder $query) => $query
                    ->where('slug', (string) $request->string('genre'))))
            ->when($request->filled('min_price'), fn (Builder $query) => $query
                ->whereRaw($priceExpression.' >= CAST(? AS DECIMAL(15,2))', [$request->input('min_price')]))
            ->when($request->filled('max_price'), fn (Builder $query) => $query
                ->whereRaw($priceExpression.' <= CAST(? AS DECIMAL(15,2))', [$request->input('max_price')]));

        match ($request->input('sort', 'latest')) {
            'oldest' => $games->orderBy('release_date'),
            'price_asc' => $games->orderByRaw($priceExpression.' ASC'),
            'price_desc' => $games->orderByRaw($priceExpression.' DESC'),
            'discount' => $games->orderByDesc('discount_percent'),
            default => $games->latest('release_date'),
        };

        return view('storefront.catalog.index', [
            'games' => $games->paginate(12)->withQueryString(),
            'genres' => Genre::query()
                ->whereHas('games', fn ($query) => $query->published())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Game $game): View
    {
        abort_unless($game->status === 'published', 404);

        $game->load([
            'developer',
            'publisher',
            'genres',
            'images',
            'reviews' => fn ($query) => $query
                ->where('status', 'published')
                ->with('user')
                ->latest(),
        ]);

        $relatedGames = Game::query()
            ->published()
            ->whereKeyNot($game->id)
            ->whereHas('genres', fn ($query) => $query->whereIn('genres.id', $game->genres->modelKeys()))
            ->with(['developer', 'genres'])
            ->take(4)
            ->get();

        return view('storefront.catalog.show', compact('game', 'relatedGames'));
    }
}
