<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ReviewRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(Request $request, Game $game): View
    {
        $this->ensureOwned($request, $game);
        $review = $request->user()->reviews()->where('game_id', $game->id)->first();

        return view('customer.reviews.form', compact('game', 'review'));
    }

    public function store(ReviewRequest $request, Game $game): RedirectResponse
    {
        $this->ensureOwned($request, $game);
        $request->user()->reviews()->updateOrCreate(
            ['game_id' => $game->id],
            [...$request->validated(), 'status' => 'pending'],
        );

        return redirect()->route('library.index')->with('success', 'Review disimpan dan menunggu moderasi admin.');
    }

    private function ensureOwned(Request $request, Game $game): void
    {
        abort_unless($request->user()->libraries()->where('game_id', $game->id)->exists(), 403);
    }
}
