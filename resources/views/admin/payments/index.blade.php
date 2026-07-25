@extends('layouts.app')

@section('title', 'Payment — Admin DayatGames')

@section('content')
    <header>
        <div>
            <p>Transaksi</p>
            <h1>Riwayat payment</h1>
            <p>Pantau pembayaran simulasi yang menunggu, terdeteksi otomatis, atau gagal.</p>
        </div>
    </header>

    <nav class="mb-5 flex flex-wrap gap-2" aria-label="Filter status payment">
        @foreach([
            'all' => 'Semua',
            'pending' => 'Menunggu pembayaran',
            'verified' => 'Terdeteksi',
            'failed' => 'Gagal / dibatalkan',
        ] as $value => $label)
            <a href="{{ route('admin.payments.index', ['status' => $value]) }}" class="rounded-lg border px-3 py-2 text-sm font-semibold {{ $status === $value ? 'border-cyan-500/40 bg-cyan-500/10 text-cyan-200' : 'border-slate-700 text-slate-400' }}">{{ $label }}</a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.payments.index') }}" role="search">
        <input type="hidden" name="status" value="{{ $status }}">
        <label for="payment-search">Cari payment</label>
        <input id="payment-search" name="search" value="{{ request('search') }}" placeholder="Kode order, referensi simulasi, atau VA">
        <button type="submit">Cari</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Game & order</th>
                <th>Customer</th>
                <th>Metode</th>
                <th>Jumlah</th>
                <th>Terdeteksi pada</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                @php
                    $firstItem = $payment->order->items->first();
                    $additional = max(0, $payment->order->items->count() - 1);
                    $state = match ($payment->status) {
                        'verified' => $payment->verified_by ? 'Diverifikasi admin' : 'Terdeteksi otomatis',
                        'failed' => 'Gagal / dibatalkan',
                        default => 'Menunggu pembayaran',
                    };
                @endphp
                <tr>
                    <td>
                        @if($firstItem?->game)
                            <img src="{{ $firstItem->game->coverUrl() }}" alt="Cover {{ $firstItem->game_title }}">
                        @endif
                        <strong>{{ $payment->order->order_code }}</strong>
                        @if($additional > 0)<span>+{{ $additional }}</span>@endif
                    </td>
                    <td>{{ $payment->order->user->email }}</td>
                    <td>{{ str_replace('_', ' ', strtoupper($payment->payment_method)) }}</td>
                    <td><strong>Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</strong></td>
                    <td>{{ $payment->paid_at?->format('d/m/Y H:i') ?: '-' }}</td>
                    <td><span>{{ $state }}</span></td>
                    <td><a href="{{ route('admin.payments.show', $payment) }}">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada payment pada status ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-6">{{ $payments->links() }}</div>
@endsection
