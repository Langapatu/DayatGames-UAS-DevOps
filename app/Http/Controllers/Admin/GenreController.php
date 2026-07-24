<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenreRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GenreController extends Controller
{
    public function index(Request $request): View
    {
        $genres = Genre::query()
            ->withCount('games')
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.genres.index', compact('genres'));
    }

    public function create(): View
    {
        return view('admin.genres.form', ['genre' => new Genre()]);
    }

    public function store(GenreRequest $request): RedirectResponse
    {
        Genre::create($request->validated());

        return redirect()->route('admin.genres.index')->with('success', 'Genre berhasil dibuat.');
    }

    public function edit(Genre $genre): View
    {
        return view('admin.genres.form', compact('genre'));
    }

    public function update(GenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()->route('admin.genres.index')->with('success', 'Genre berhasil diperbarui.');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        $genre->delete();

        return back()->with('success', 'Genre berhasil dihapus.');
    }
}

