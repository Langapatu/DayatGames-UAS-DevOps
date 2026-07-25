<?php

namespace App\Services;

use App\Models\Order;

final class PaymentInstructionService
{
    public function for(Order $order): array
    {
        $order->loadMissing('payment');
        $method = $order->payment?->payment_method;
        $config = config("payments.methods.{$method}", [
            'label' => 'Pembayaran',
            'destination_label' => 'Tujuan pembayaran demo',
            'destination' => '-',
            'account_name' => 'DayatGames Demo',
            'steps' => ['Hubungi admin DayatGames untuk memeriksa metode pembayaran demo.'],
        ]);

        return [
            'label' => $config['label'],
            'destination_label' => $config['destination_label'],
            'destination' => $method === 'virtual_account'
                ? ($order->payment?->virtual_account_number ?? '-')
                : ($config['destination'] ?? '-'),
            'account_name' => $config['account_name'],
            'steps' => $config['steps'],
        ];
    }
}
