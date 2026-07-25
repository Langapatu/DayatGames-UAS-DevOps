<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GameRequest;
use App\Models\Developer;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(Request $request): View
    {
        $games = Game::query()
            ->with(['developer', 'publisher'])
            ->when($request->filled('search'), fn ($query) => $query
                ->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query
                ->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.games.index', compact('games'));
    }

    public function create(): View
    {
        return $this->formView(new Game());
    }

    public function store(GameRequest $request): RedirectResponse
    {
        $game = DB::transaction(function () use ($request): Game {
            $data = $this->gameData($request);
            $data['developer_id'] = $this->resolveParty(
                Developer::class,
                $request->integer('developer_id') ?: null,
                $request->validated('new_developer_name'),
            );
            $data['publisher_id'] = $this->resolveParty(
                Publisher::class,
                $request->integer('publisher_id') ?: null,
                $request->validated('new_publisher_name'),
            );
            $game = Game::create($data);
            $game->genres()->sync($request->validated('genres'));

            return $game;
        });

        return redirect()->route('admin.games.edit', $game)->with('success', 'Game berhasil dibuat.');
    }

    public function show(Game $game): View
    {
        $game->load(['developer', 'publisher', 'genres', 'images']);

        return view('admin.games.show', compact('game'));
    }

    public function edit(Game $game): View
    {
        $game->load('genres');

        return $this->formView($game);
    }

    public function update(GameRequest $request, Game $game): RedirectResponse
    {
        DB::transaction(function () use ($request, $game): void {
            $data = $this->gameData($request, $game);
            $data['developer_id'] = $this->resolveParty(
                Developer::class,
                $request->integer('developer_id') ?: null,
                $request->validated('new_developer_name'),
            );
            $data['publisher_id'] = $this->resolveParty(
                Publisher::class,
                $request->integer('publisher_id') ?: null,
                $request->validated('new_publisher_name'),
            );
            $game->update($data);
            $game->genres()->sync($request->validated('genres'));
        });

        return redirect()->route('admin.games.edit', $game)->with('success', 'Game berhasil diperbarui.');
    }

    public function destroy(Game $game): RedirectResponse
    {
        if ($game->orderItems()->exists() || $game->libraries()->exists()) {
            return back()->with('error', 'Game memiliki riwayat transaksi atau library dan tidak dapat dihapus.');
        }

        $this->deleteManagedImage($game->cover_image);
        $this->deleteManagedImage($game->hero_image);
        $game->delete();

        return redirect()->route('admin.games.index')->with('success', 'Game berhasil dihapus.');
    }

    private function formView(Game $game): View
    {
        return view('admin.games.form', [
            'game' => $game,
            'developers' => Developer::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('name')->get(),
            'genres' => Genre::orderBy('name')->get(),
        ]);
    }

    private function gameData(GameRequest $request, ?Game $game = null): array
    {
        $data = $request->safe()->except([
            'genres',
            'cover_image',
            'hero_image',
            'new_developer_name',
            'new_publisher_name',
        ]);

        foreach (['cover_image', 'hero_image'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $this->deleteManagedImage($game?->{$field});
            $data[$field] = 'storage/'.$request->file($field)->store('games', 'public');
        }

        return $data;
    }

    private function resolveParty(string $modelClass, ?int $id, ?string $newName): int
    {
        if ($id) {
            return $id;
        }

        $name = trim((string) $newName);
        $slug = Str::slug($name);

        return $modelClass::query()
            ->firstOrCreate(['slug' => $slug], ['name' => $name])
            ->id;
    }

    private function deleteManagedImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('storage/')));
        }
    }
}
