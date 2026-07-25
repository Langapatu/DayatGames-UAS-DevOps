<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VoucherRequest;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $state = in_array($request->string('state')->toString(), ['active', 'expired'], true)
            ? $request->string('state')->toString()
            : null;

        $vouchers = Voucher::query()
            ->withCount('orders')
            ->when($request->filled('search'), fn ($query) => $query
                ->where('code', 'like', '%'.trim((string) $request->string('search')).'%'))
            ->when($state === 'active', fn ($query) => $query->valid())
            ->when($state === 'expired', fn ($query) => $query
                ->whereDate('expires_at', '<', today()))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function create(): View
    {
        return view('admin.vouchers.form', ['voucher' => new Voucher]);
    }

    public function store(VoucherRequest $request): RedirectResponse
    {
        Voucher::create($request->validated());

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('admin.vouchers.form', compact('voucher'));
    }

    public function update(VoucherRequest $request, Voucher $voucher): RedirectResponse
    {
        $voucher->update($request->validated());

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $voucher->delete();

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher dihapus. Snapshot pada order lama tetap tersimpan.');
    }
}
