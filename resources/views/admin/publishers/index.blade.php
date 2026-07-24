@extends('layouts.app')

@section('title', 'Kelola Publisher — DayatGames')

@section('content')
    <header>
        <div><h1>Publisher</h1><p>Kelola penerbit game.</p></div>
        <a href="{{ route('admin.publishers.create') }}">Tambah publisher</a>
    </header>

    <form method="GET" action="{{ route('admin.publishers.index') }}" role="search">
        <label for="publisher-search">Cari publisher</label>
        <input id="publisher-search" name="search" type="search" value="{{ request('search') }}">
        <button type="submit">Cari</button>
    </form>

    <table>
        <thead><tr><th>Nama</th><th>Website</th><th>Game</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($publishers as $publisher)
                <tr>
                    <td>{{ $publisher->name }}</td>
                    <td>{{ $publisher->website ?: '—' }}</td>
                    <td>{{ $publisher->games_count }}</td>
                    <td>
                        <a href="{{ route('admin.publishers.edit', $publisher) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.publishers.destroy', $publisher) }}" data-confirm="Hapus publisher ini?">
                            @csrf @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada publisher yang sesuai.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $publishers->links() }}
@endsection

