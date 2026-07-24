@props(['title', 'message'])

<section class="rounded-2xl border border-dashed border-slate-700 bg-slate-900/60 p-10 text-center">
    <h2 class="text-xl font-semibold text-slate-100">{{ $title }}</h2>
    <p class="mt-2 text-slate-400">{{ $message }}</p>
    {{ $slot }}
</section>
