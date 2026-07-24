@extends('layouts.app')

@section('title', 'Kelola Genre — DayatGames')

@section('content')
    <header>
        <div>
            <h1>Genre</h1>
            <p>Kelola klasifikasi katalog game.</p>
        </div>
        <a href="{{ route('admin.genres.create') }}">Tambah genre</a>
    </header>

    <form method="GET" action="{{ route('admin.genres.index') }}" role="search">
        <label for="genre-search">Cari genre</label>
        <input id="genre-search" name="search" type="search" value="{{ request('search') }}">
        <button type="submit">Cari</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Slug</th>
                <th>Game</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($genres as $genre)
                <tr>
                    <td>{{ $genre->name }}</td>
                    <td>{{ $genre->slug }}</td>
                    <td>{{ $genre->games_count }}</td>
                    <td>
                        <a href="{{ route('admin.genres.edit', $genre) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.genres.destroy', $genre) }}" data-confirm="Hapus genre ini?">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada genre yang sesuai.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $genres->links() }}
@endsection

