<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'games' => Game::count(),
            'customers' => User::where('role', 'customer')->count(),
            'orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'verified_payments' => Payment::where('status', 'verified')->count(),
            'revenue' => Payment::where('status', 'verified')->sum('amount'),
        ];

        $recentOrders = Order::with('user')->latest('ordered_at')->limit(5)->get();
        $topGames = Game::query()
            ->withCount('orderItems')
            ->withSum('orderItems', 'subtotal')
            ->orderByDesc('order_items_count')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'topGames'));
    }
}
