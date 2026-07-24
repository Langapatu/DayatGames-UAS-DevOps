@extends('layouts.app')

@section('title', ($game->exists ? 'Edit' : 'Tambah').' Game — DayatGames')

@section('content')
    @php
        $selectedGenres = array_map('intval', old('genres', $game->exists ? $game->genres->pluck('id')->all() : []));
    @endphp

    <h1>{{ $game->exists ? 'Edit game' : 'Tambah game' }}</h1>

    <form method="POST"
          action="{{ $game->exists ? route('admin.games.update', $game) : route('admin.games.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if($game->exists) @method('PUT') @endif

        <fieldset>
            <legend>Identitas game</legend>
            <div>
                <label for="title">Judul</label>
                <input id="title" name="title" type="text" value="{{ old('title', $game->title) }}" required>
                @error('title') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="slug">Slug</label>
                <input id="slug" name="slug" type="text" value="{{ old('slug', $game->slug) }}">
                @error('slug') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="steam_app_id">Steam App ID</label>
                <input id="steam_app_id" name="steam_app_id" type="number" min="1" value="{{ old('steam_app_id', $game->steam_app_id) }}">
                @error('steam_app_id') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="developer_id">Developer</label>
                <select id="developer_id" name="developer_id" required>
                    <option value="">Pilih developer</option>
                    @foreach($developers as $developer)
                        <option value="{{ $developer->id }}" @selected((int) old('developer_id', $game->developer_id) === $developer->id)>{{ $developer->name }}</option>
                    @endforeach
                </select>
                @error('developer_id') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="publisher_id">Publisher</label>
                <select id="publisher_id" name="publisher_id" required>
                    <option value="">Pilih publisher</option>
                    @foreach($publishers as $publisher)
                        <option value="{{ $publisher->id }}" @selected((int) old('publisher_id', $game->publisher_id) === $publisher->id)>{{ $publisher->name }}</option>
                    @endforeach
                </select>
                @error('publisher_id') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <fieldset>
                <legend>Genre</legend>
                @foreach($genres as $genre)
                    <label>
                        <input name="genres[]" type="checkbox" value="{{ $genre->id }}" @checked(in_array($genre->id, $selectedGenres, true))>
                        {{ $genre->name }}
                    </label>
                @endforeach
                @error('genres') <p role="alert">{{ $message }}</p> @enderror
                @error('genres.*') <p role="alert">{{ $message }}</p> @enderror
            </fieldset>
        </fieldset>

        <fieldset>
            <legend>Deskripsi</legend>
            <div>
                <label for="short_description">Deskripsi singkat</label>
                <textarea id="short_description" name="short_description" rows="3" required>{{ old('short_description', $game->short_description) }}</textarea>
                @error('short_description') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="description">Deskripsi lengkap</label>
                <textarea id="description" name="description" rows="9" required>{{ old('description', $game->description) }}</textarea>
                @error('description') <p role="alert">{{ $message }}</p> @enderror
            </div>
        </fieldset>

        <fieldset>
            <legend>Harga</legend>
            <div>
                <label for="original_price">Harga normal</label>
                <input id="original_price" name="original_price" type="number" min="0" step="0.01" value="{{ old('original_price', $game->original_price) }}" required>
                @error('original_price') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="discount_price">Harga diskon</label>
                <input id="discount_price" name="discount_price" type="number" min="0" step="0.01" value="{{ old('discount_price', $game->discount_price) }}">
                @error('discount_price') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="discount_percent">Persentase diskon</label>
                <input id="discount_percent" name="discount_percent" type="number" min="0" max="100" value="{{ old('discount_percent', $game->discount_percent ?? 0) }}" required>
                @error('discount_percent') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="price_checked_at">Waktu pemeriksaan harga</label>
                <input id="price_checked_at" name="price_checked_at" type="datetime-local" value="{{ old('price_checked_at', $game->price_checked_at?->format('Y-m-d\TH:i')) }}">
                @error('price_checked_at') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="price_source_url">URL sumber harga</label>
                <input id="price_source_url" name="price_source_url" type="url" value="{{ old('price_source_url', $game->price_source_url) }}">
                @error('price_source_url') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <label>
                <input name="price_is_demo" type="checkbox" value="1" @checked(old('price_is_demo', $game->exists ? $game->price_is_demo : true))>
                Harga adalah data demo
            </label>
        </fieldset>

        <fieldset>
            <legend>Platform dan publikasi</legend>
            <div>
                <label for="release_date">Tanggal rilis</label>
                <input id="release_date" name="release_date" type="date" value="{{ old('release_date', $game->release_date?->format('Y-m-d')) }}">
                @error('release_date') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="platform">Platform</label>
                <input id="platform" name="platform" type="text" value="{{ old('platform', $game->platform ?: 'PC') }}" required>
                @error('platform') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="operating_system">Sistem operasi</label>
                <input id="operating_system" name="operating_system" type="text" value="{{ old('operating_system', $game->operating_system ?: 'Windows') }}">
                @error('operating_system') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="draft" @selected(old('status', $game->status ?: 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $game->status) === 'published')>Published</option>
                </select>
                @error('status') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <label>
                <input name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $game->is_featured))>
                Featured game
            </label>
        </fieldset>

        <fieldset>
            <legend>Gambar</legend>
            <div>
                <label for="cover_image">Cover (maksimal 4 MB)</label>
                <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp">
                @error('cover_image') <p role="alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="hero_image">Hero (maksimal 6 MB)</label>
                <input id="hero_image" name="hero_image" type="file" accept="image/jpeg,image/png,image/webp">
                @error('hero_image') <p role="alert">{{ $message }}</p> @enderror
            </div>
        </fieldset>

        <button type="submit">Simpan game</button>
        <a href="{{ route('admin.games.index') }}">Batal</a>
    </form>
@endsection

