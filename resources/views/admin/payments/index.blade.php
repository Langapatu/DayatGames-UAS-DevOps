@extends('layouts.app')

@section('title', 'Payment — Admin DayatGames')

@section('content')
    <header>
        <div>
            <p>Transaksi</p>
            <h1>Verifikasi payment</h1>
            <p>Antrean utama hanya menampilkan bukti yang sudah siap diperiksa.</p>
        </div>
    </header>

    <nav class="mb-5 flex flex-wrap gap-2" aria-label="Filter antrean payment">
        @foreach([
            'ready' => 'Siap diverifikasi',
            'waiting' => 'Menunggu customer',
            'verified' => 'Verified',
            'failed' => 'Ditolak',
            'all' => 'Semua',
        ] as $value => $label)
            <a href="{{ route('admin.payments.index', ['queue' => $value]) }}" class="rounded-lg border px-3 py-2 text-sm font-semibold {{ $queue === $value ? 'border-cyan-500/40 bg-cyan-500/10 text-cyan-200' : 'border-slate-700 text-slate-400' }}">{{ $label }}</a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.payments.index') }}" role="search">
        <input type="hidden" name="queue" value="{{ $queue }}">
        <label for="payment-search">Cari payment</label>
        <input id="payment-search" name="search" value="{{ request('search') }}" placeholder="Kode order, referensi, atau VA">
        <button type="submit">Cari</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Game & order</th>
                <th>Customer</th>
                <th>Metode</th>
                <th>Jumlah</th>
                <th>Dikirim</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                @php
                    $firstItem = $payment->order->items->first();
                    $additional = max(0, $payment->order->items->count() - 1);
                    $state = $payment->status === 'pending'
                        ? ($payment->payment_proof ? 'Siap diverifikasi' : 'Menunggu customer')
                        : ($payment->status === 'verified' ? 'Verified' : 'Ditolak');
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
                    <td>{{ $payment->paid_at?->format('d/m/Y H:i') ?: 'Belum dikirim' }}</td>
                    <td><span>{{ $state }}</span></td>
                    <td><a href="{{ route('admin.payments.show', $payment) }}">Periksa</a></td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada payment pada antrean ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-6">{{ $payments->links() }}</div>
@endsection
