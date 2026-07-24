@extends('layouts.app')

@section('title', $game->title.' — Admin DayatGames')

@section('content')
    <article>
        @if($game->cover_image)
            <img src="{{ $game->coverUrl() }}" alt="Cover {{ $game->title }}" width="280" loading="lazy">
        @endif
        <h1>{{ $game->title }}</h1>
        <p>{{ $game->short_description }}</p>
        <dl>
            <div><dt>Developer</dt><dd>{{ $game->developer->name }}</dd></div>
            <div><dt>Publisher</dt><dd>{{ $game->publisher->name }}</dd></div>
            <div><dt>Genre</dt><dd>{{ $game->genres->pluck('name')->join(', ') }}</dd></div>
            <div><dt>Status</dt><dd>{{ ucfirst($game->status) }}</dd></div>
            <div><dt>Harga</dt><dd>Rp{{ number_format((float) $game->currentPrice(), 0, ',', '.') }}</dd></div>
            <div><dt>Sumber</dt><dd>{{ $game->price_is_demo ? 'Data demo' : 'Harga terverifikasi' }}</dd></div>
        </dl>
        <div>{!! nl2br(e($game->description)) !!}</div>
        <a href="{{ route('admin.games.edit', $game) }}">Edit game</a>
        <a href="{{ route('admin.games.index') }}">Kembali</a>
    </article>
@endsection
