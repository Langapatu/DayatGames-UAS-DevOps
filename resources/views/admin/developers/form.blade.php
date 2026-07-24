@extends('layouts.app')

@section('title', ($developer->exists ? 'Edit' : 'Tambah').' Developer — DayatGames')

@section('content')
    <h1>{{ $developer->exists ? 'Edit developer' : 'Tambah developer' }}</h1>
    <form method="POST" action="{{ $developer->exists ? route('admin.developers.update', $developer) : route('admin.developers.store') }}">
        @csrf
        @if($developer->exists) @method('PUT') @endif
        <div>
            <label for="name">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name', $developer->name) }}" required>
            @error('name') <p role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="slug">Slug</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug', $developer->slug) }}">
            @error('slug') <p role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="website">Website</label>
            <input id="website" name="website" type="url" value="{{ old('website', $developer->website) }}">
            @error('website') <p role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="5">{{ old('description', $developer->description) }}</textarea>
            @error('description') <p role="alert">{{ $message }}</p> @enderror
        </div>
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.developers.index') }}">Batal</a>
    </form>
@endsection

