@extends('layouts.app')

@section('title', 'Kelola Developer — DayatGames')

@section('content')
    <header>
        <div><h1>Developer</h1><p>Kelola studio pengembang.</p></div>
        <a href="{{ route('admin.developers.create') }}">Tambah developer</a>
    </header>

    <form method="GET" action="{{ route('admin.developers.index') }}" role="search">
        <label for="developer-search">Cari developer</label>
        <input id="developer-search" name="search" type="search" value="{{ request('search') }}">
        <button type="submit">Cari</button>
    </form>

    <table>
        <thead><tr><th>Nama</th><th>Website</th><th>Game</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($developers as $developer)
                <tr>
                    <td>{{ $developer->name }}</td>
                    <td>{{ $developer->website ?: '—' }}</td>
                    <td>{{ $developer->games_count }}</td>
                    <td>
                        <a href="{{ route('admin.developers.edit', $developer) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.developers.destroy', $developer) }}" data-confirm="Hapus developer ini?">
                            @csrf @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada developer yang sesuai.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $developers->links() }}
@endsection

