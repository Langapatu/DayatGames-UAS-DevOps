@extends('layouts.app')

@section('title', 'Profil — DayatGames')

@section('content')
    @php($user = auth()->user())

    <section class="profile-shell" aria-labelledby="profile-title">
        <header class="profile-heading">
            <div>
                <p class="profile-eyebrow">Pengaturan akun</p>
                <h1 id="profile-title">Profil saya</h1>
                <p>Kelola identitas dan informasi kontak yang digunakan di DayatGames.</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="profile-back-link">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Kembali ke katalog
            </a>
        </header>

        <div class="profile-layout">
            <aside class="profile-summary">
                <div class="profile-avatar">
                    @if($user->avatar)
                        <img src="{{ asset('storage/'.$user->avatar) }}" alt="Avatar {{ $user->name }}">
                    @else
                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>
                <span class="profile-role">{{ $user->isAdmin() ? 'Administrator' : 'Customer' }}</span>

                <div class="profile-account-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 4.5 6v5c0 4.8 3.1 8.6 7.5 10 4.4-1.4 7.5-5.2 7.5-10V6L12 3Z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                    <div>
                        <strong>Akun terlindungi</strong>
                        <span>Data profil hanya digunakan untuk kebutuhan akun dan transaksi.</span>
                    </div>
                </div>
            </aside>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-form">
                @csrf
                @method('PATCH')

                <div class="profile-form-heading">
                    <div>
                        <h2>Informasi pribadi</h2>
                        <p>Pastikan nama, email, dan nomor telepon Anda tetap terbaru.</p>
                    </div>
                    <span>Terakhir diperbarui {{ $user->updated_at->translatedFormat('d M Y') }}</span>
                </div>

                <div class="profile-fields">
                    <div class="profile-field">
                        <label for="name">Nama lengkap</label>
                        <div class="profile-input">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name">
                        </div>
                        @error('name') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="profile-field">
                        <label for="email">Alamat email</label>
                        <div class="profile-input">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                        </div>
                        @error('email') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="profile-field profile-field-wide">
                        <label for="phone">Nomor telepon <span>Opsional</span></label>
                        <div class="profile-input">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.4 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/></svg>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" placeholder="+62 812 3456 7890" autocomplete="tel">
                        </div>
                        @error('phone') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="profile-field profile-field-wide">
                        <label for="avatar">Foto profil</label>
                        <label for="avatar" class="profile-upload">
                            <span class="profile-upload-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M20 15v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-4"/></svg>
                            </span>
                            <span>
                                <strong>Pilih foto baru</strong>
                                <small>JPG, PNG, atau WebP · maksimal 2 MB</small>
                            </span>
                            <span class="profile-upload-button" data-profile-upload-label>Pilih file</span>
                            <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" data-profile-upload>
                        </label>
                        @error('avatar') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="profile-actions">
                    <p>Perubahan akan langsung diterapkan pada akun Anda.</p>
                    <button type="submit">Simpan perubahan</button>
                </div>
            </form>
        </div>
    </section>
@endsection
