<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->where('role', 'customer')
            ->withCount(['orders', 'libraries'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = (string) $request->string('search');
                $query->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'customer', 404);
        $user->load(['orders.payment', 'libraries.game']);

        return view('admin.users.show', compact('user'));
    }
}
