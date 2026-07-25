@extends('layouts.app')

@section('title', 'Voucher — Admin DayatGames')

@section('content')
    <header>
        <div>
            <p>Promosi</p>
            <h1>Voucher</h1>
            <p>Kelola potongan harga persentase untuk checkout DayatGames.</p>
        </div>
        <a href="{{ route('admin.vouchers.create') }}">Tambah voucher</a>
    </header>

    <form method="GET" action="{{ route('admin.vouchers.index') }}" role="search">
        <label for="voucher-search">Cari kode</label>
        <input id="voucher-search" name="search" type="search" value="{{ request('search') }}" placeholder="Contoh: HEMAT20">
        <label for="voucher-state">Status</label>
        <select id="voucher-state" name="state">
            <option value="">Semua</option>
            <option value="active" @selected(request('state') === 'active')>Aktif</option>
            <option value="expired" @selected(request('state') === 'expired')>Kedaluwarsa</option>
        </select>
        <button type="submit">Terapkan</button>
        @if(request()->hasAny(['search', 'state']))
            <a href="{{ route('admin.vouchers.index') }}">Reset</a>
        @endif
    </form>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Diskon</th>
                <th>Berlaku sampai</th>
                <th>Status</th>
                <th>Digunakan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vouchers as $voucher)
                @php
                    $expired = $voucher->expires_at->isBefore(today());
                    $available = $voucher->is_active && !$expired;
                @endphp
                <tr>
                    <td><strong>{{ $voucher->code }}</strong></td>
                    <td>{{ $voucher->discount_percent }}%</td>
                    <td>{{ $voucher->expires_at->format('d/m/Y') }}</td>
                    <td>
                        <span>{{ $available ? 'Aktif' : ($expired ? 'Kedaluwarsa' : 'Nonaktif') }}</span>
                    </td>
                    <td>{{ $voucher->orders_count }} order</td>
                    <td>
                        <a href="{{ route('admin.vouchers.edit', $voucher) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" data-confirm="Hapus voucher {{ $voucher->code }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Belum ada voucher yang sesuai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-6">{{ $vouchers->links() }}</div>
@endsection
