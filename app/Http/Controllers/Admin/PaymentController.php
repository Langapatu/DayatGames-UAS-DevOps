<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array(
            $request->string('status')->toString(),
            ['pending', 'verified', 'failed', 'all'],
            true,
        ) ? $request->string('status')->toString() : 'all';

        $payments = Payment::query()
            ->with(['order.user', 'order.items.game', 'verifier'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));
                $query->where(fn ($query) => $query
                    ->where('payment_reference', 'like', '%'.$search.'%')
                    ->orWhere('virtual_account_number', 'like', '%'.$search.'%')
                    ->orWhereHas('order', fn ($query) => $query
                        ->where('order_code', 'like', '%'.$search.'%')));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'status'));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['order.user', 'order.items.game', 'verifier']);

        return view('admin.payments.show', compact('payment'));
    }
}
