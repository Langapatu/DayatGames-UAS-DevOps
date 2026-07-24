<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $libraries = $request->user()
            ->libraries()
            ->with(['game.developer', 'game.genres', 'order'])
            ->latest('purchased_at')
            ->paginate(12);

        return view('customer.library.index', compact('libraries'));
    }
}
