<?php

namespace App\Services;

use App\Models\Voucher;
use Illuminate\Support\Str;

final class VoucherService
{
    public function normalize(?string $code): ?string
    {
        $normalized = Str::upper(trim((string) $code));

        return $normalized === '' ? null : $normalized;
    }

    public function findValid(?string $code): ?Voucher
    {
        $normalized = $this->normalize($code);

        return $normalized
            ? Voucher::query()->valid()->where('code', $normalized)->first()
            : null;
    }

    public function discount(float $subtotal, Voucher $voucher): float
    {
        return min($subtotal, round($subtotal * $voucher->discount_percent / 100, 2));
    }
}
