@extends('layouts.app')

@section('title', ($publisher->exists ? 'Edit' : 'Tambah').' Publisher — DayatGames')

@section('content')
    <h1>{{ $publisher->exists ? 'Edit publisher' : 'Tambah publisher' }}</h1>
    <form method="POST" action="{{ $publisher->exists ? route('admin.publishers.update', $publisher) : route('admin.publishers.store') }}">
        @csrf
        @if($publisher->exists) @method('PUT') @endif
        <div>
            <label for="name">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name', $publisher->name) }}" required>
            @error('name') <p role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="slug">Slug</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug', $publisher->slug) }}">
            @error('slug') <p role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="website">Website</label>
            <input id="website" name="website" type="url" value="{{ old('website', $publisher->website) }}">
            @error('website') <p role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="5">{{ old('description', $publisher->description) }}</textarea>
            @error('description') <p role="alert">{{ $message }}</p> @enderror
        </div>
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.publishers.index') }}">Batal</a>
    </form>
@endsection

