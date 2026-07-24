@extends('layouts.app')

@section('title', 'Profil — DayatGames')

@section('content')
    <section aria-labelledby="profile-title">
        <h1 id="profile-title">Profil saya</h1>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div>
                <label for="name">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name', auth()->user()->name) }}" required>
                @error('name') <p role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" required>
                @error('email') <p role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone">Nomor telepon</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', auth()->user()->phone) }}">
                @error('phone') <p role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="avatar">Avatar (JPG, PNG, atau WebP; maksimal 2 MB)</label>
                <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp">
                @error('avatar') <p role="alert">{{ $message }}</p> @enderror
            </div>

            <button type="submit">Simpan profil</button>
        </form>
    </section>
@endsection

