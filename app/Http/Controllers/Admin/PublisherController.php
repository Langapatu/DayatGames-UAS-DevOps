<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublisherRequest;
use App\Models\Publisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublisherController extends Controller
{
    public function index(Request $request): View
    {
        $publishers = Publisher::query()
            ->withCount('games')
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.publishers.index', compact('publishers'));
    }

    public function create(): View
    {
        return view('admin.publishers.form', ['publisher' => new Publisher()]);
    }

    public function store(PublisherRequest $request): RedirectResponse
    {
        Publisher::create($request->validated());

        return redirect()->route('admin.publishers.index')->with('success', 'Publisher berhasil dibuat.');
    }

    public function edit(Publisher $publisher): View
    {
        return view('admin.publishers.form', compact('publisher'));
    }

    public function update(PublisherRequest $request, Publisher $publisher): RedirectResponse
    {
        $publisher->update($request->validated());

        return redirect()->route('admin.publishers.index')->with('success', 'Publisher berhasil diperbarui.');
    }

    public function destroy(Publisher $publisher): RedirectResponse
    {
        if ($publisher->games()->exists()) {
            return back()->with('error', 'Publisher masih digunakan oleh game dan tidak dapat dihapus.');
        }

        $publisher->delete();

        return back()->with('success', 'Publisher berhasil dihapus.');
    }
}

