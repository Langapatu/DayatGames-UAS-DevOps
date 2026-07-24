@extends('layouts.app')

@section('title', 'Review '.$game->title.' — DayatGames')

@section('content')
    <div class="mx-auto max-w-2xl">
        <p class="text-sm font-semibold uppercase tracking-widest text-amber-300">Review pemilik</p>
        <h1 class="text-4xl font-black text-white">{{ $game->title }}</h1>
        <form method="POST" action="{{ route('reviews.store', $game) }}" class="mt-8 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            <label><span class="mb-2 block font-semibold text-white">Rating</span><select name="rating" required class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white">@for($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}" @selected((int) old('rating', $review?->rating ?? 5) === $rating)>{{ $rating }} / 5</option>@endfor</select></label>
            <label class="mt-5 block"><span class="mb-2 block font-semibold text-white">Komentar</span><textarea name="comment" rows="7" maxlength="2000" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white">{{ old('comment', $review?->comment) }}</textarea></label>
            <p class="mt-3 text-sm text-slate-400">Review baru atau hasil edit akan berstatus pending sampai dimoderasi admin.</p>
            <button class="mt-6 rounded-xl bg-violet-600 px-5 py-3 font-bold text-white">Simpan review</button>
        </form>
    </div>
@endsection
