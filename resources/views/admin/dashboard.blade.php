@extends('layouts.app')

@section('title', 'Dashboard Admin — DayatGames')

@section('content')
    <section aria-labelledby="dashboard-title">
        <h1 id="dashboard-title">Dashboard admin</h1>

        <dl>
            <div><dt>Game</dt><dd>{{ $stats['games'] }}</dd></div>
            <div><dt>Customer</dt><dd>{{ $stats['customers'] }}</dd></div>
            <div><dt>Order</dt><dd>{{ $stats['orders'] }}</dd></div>
            <div><dt>Order pending</dt><dd>{{ $stats['pending_orders'] }}</dd></div>
            <div><dt>Payment verified</dt><dd>{{ $stats['verified_payments'] }}</dd></div>
            <div><dt>Pendapatan</dt><dd>Rp{{ number_format((float) $stats['revenue'], 0, ',', '.') }}</dd></div>
        </dl>
    </section>

    <section aria-labelledby="recent-orders-title">
        <h2 id="recent-orders-title">Order terbaru</h2>
        @if($recentOrders->isEmpty())
            <p>Belum ada order.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td>{{ $order->order_code }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
@endsection

