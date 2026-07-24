@extends('layouts.app')

@section('title', ($genre->exists ? 'Edit' : 'Tambah').' Genre — DayatGames')

@section('content')
    <h1>{{ $genre->exists ? 'Edit genre' : 'Tambah genre' }}</h1>

    <form method="POST" action="{{ $genre->exists ? route('admin.genres.update', $genre) : route('admin.genres.store') }}">
        @csrf
        @if($genre->exists) @method('PUT') @endif

        <div>
            <label for="name">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name', $genre->name) }}" required>
            @error('name') <p role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="slug">Slug (opsional; dibuat dari nama)</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug', $genre->slug) }}">
            @error('slug') <p role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="5">{{ old('description', $genre->description) }}</textarea>
            @error('description') <p role="alert">{{ $message }}</p> @enderror
        </div>

        <button type="submit">Simpan</button>
        <a href="{{ route('admin.genres.index') }}">Batal</a>
    </form>
@endsection

