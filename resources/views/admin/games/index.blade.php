@extends('layouts.app')

@section('title', 'Kelola Game — DayatGames')

@section('content')
    <header>
        <div><h1>Games</h1><p>Kelola katalog, harga, status, dan featured game.</p></div>
        <a href="{{ route('admin.games.create') }}">Tambah game</a>
    </header>

    <form method="GET" action="{{ route('admin.games.index') }}" role="search">
        <label for="game-search">Cari judul</label>
        <input id="game-search" name="search" type="search" value="{{ request('search') }}">
        <label for="game-status">Status</label>
        <select id="game-status" name="status">
            <option value="">Semua</option>
            <option value="published" @selected(request('status') === 'published')>Published</option>
            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
        </select>
        <button type="submit">Terapkan</button>
    </form>

    <table>
        <thead>
            <tr><th>Game</th><th>Developer</th><th>Harga</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($games as $game)
                <tr>
                    <td>
                        @if($game->cover_image)
                            <img src="{{ asset($game->cover_image) }}" alt="" width="64" height="84" loading="lazy">
                        @endif
                        <strong>{{ $game->title }}</strong>
                        @if($game->is_featured) <span>Featured</span> @endif
                    </td>
                    <td>{{ $game->developer->name }}</td>
                    <td>
                        @if($game->discount_price)
                            <del>Rp{{ number_format((float) $game->original_price, 0, ',', '.') }}</del>
                            Rp{{ number_format((float) $game->discount_price, 0, ',', '.') }}
                        @else
                            Rp{{ number_format((float) $game->original_price, 0, ',', '.') }}
                        @endif
                    </td>
                    <td>{{ ucfirst($game->status) }}</td>
                    <td>
                        <a href="{{ route('admin.games.show', $game) }}">Lihat</a>
                        <a href="{{ route('admin.games.edit', $game) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.games.destroy', $game) }}" data-confirm="Hapus game ini?">
                            @csrf @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada game yang sesuai.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $games->links() }}
@endsection

