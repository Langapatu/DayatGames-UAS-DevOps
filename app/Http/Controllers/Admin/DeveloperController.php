<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeveloperRequest;
use App\Models\Developer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function index(Request $request): View
    {
        $developers = Developer::query()
            ->withCount('games')
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.developers.index', compact('developers'));
    }

    public function create(): View
    {
        return view('admin.developers.form', ['developer' => new Developer()]);
    }

    public function store(DeveloperRequest $request): RedirectResponse
    {
        Developer::create($request->validated());

        return redirect()->route('admin.developers.index')->with('success', 'Developer berhasil dibuat.');
    }

    public function edit(Developer $developer): View
    {
        return view('admin.developers.form', compact('developer'));
    }

    public function update(DeveloperRequest $request, Developer $developer): RedirectResponse
    {
        $developer->update($request->validated());

        return redirect()->route('admin.developers.index')->with('success', 'Developer berhasil diperbarui.');
    }

    public function destroy(Developer $developer): RedirectResponse
    {
        if ($developer->games()->exists()) {
            return back()->with('error', 'Developer masih digunakan oleh game dan tidak dapat dihapus.');
        }

        $developer->delete();

        return back()->with('success', 'Developer berhasil dihapus.');
    }
}

