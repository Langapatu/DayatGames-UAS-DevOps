<?php

return [
    'methods' => [
        'virtual_account' => [
            'label' => 'Virtual Account',
            'destination_label' => 'Nomor Virtual Account',
            'account_name' => 'DayatGames Demo',
            'steps' => [
                'Buka mobile banking atau ATM pilihan Anda.',
                'Pilih menu pembayaran Virtual Account.',
                'Masukkan nomor VA dan pastikan nominalnya sesuai.',
                'Simpan bukti transaksi simulasi untuk diunggah.',
            ],
        ],
        'bank_transfer' => [
            'label' => 'Transfer Bank',
            'destination_label' => 'Rekening demo Bank Dayat',
            'destination' => '8808 2026 0725',
            'account_name' => 'DayatGames Demo',
            'steps' => [
                'Pilih transfer antarbank pada aplikasi bank Anda.',
                'Masukkan rekening demo yang ditampilkan.',
                'Masukkan nominal tepat sesuai total pesanan.',
                'Simpan nomor referensi dan bukti transaksi simulasi.',
            ],
        ],
        'e_wallet' => [
            'label' => 'E-Wallet',
            'destination_label' => 'DayatPay Demo',
            'destination' => '0812 0000 2026',
            'account_name' => 'DayatGames Demo',
            'steps' => [
                'Buka aplikasi e-wallet pilihan Anda.',
                'Pilih menu kirim saldo.',
                'Masukkan akun demo dan nominal tepat.',
                'Simpan nomor referensi dan bukti transaksi simulasi.',
            ],
        ],
    ],
];
