<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_code',
        'checkout_token',
        'subtotal_amount',
        'voucher_id',
        'voucher_code',
        'voucher_discount_amount',
        'total_amount',
        'status',
        'ordered_at',
        'payment_due_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'voucher_discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'ordered_at' => 'datetime',
            'payment_due_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function libraries(): HasMany
    {
        return $this->hasMany(Library::class);
    }

    public function hasSubmittedProof(): bool
    {
        return filled($this->payment?->payment_proof);
    }

    public function trackingStage(): string
    {
        return match ($this->status) {
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'paid' => 'Pembayaran diterima',
            default => $this->hasSubmittedProof()
                ? 'Menunggu verifikasi'
                : 'Menunggu pembayaran',
        };
    }

    public function trackingSteps(): array
    {
        $hasProof = $this->hasSubmittedProof();
        $completed = $this->status === 'completed';
        $cancelled = $this->status === 'cancelled';

        return [
            ['label' => 'Pesanan dibuat', 'state' => 'completed'],
            [
                'label' => 'Menunggu pembayaran',
                'state' => ($hasProof || $completed) ? 'completed' : ($cancelled ? 'failed' : 'current'),
            ],
            [
                'label' => 'Bukti pembayaran dikirim',
                'state' => $hasProof ? 'completed' : ($cancelled ? 'failed' : 'future'),
            ],
            [
                'label' => 'Menunggu verifikasi',
                'state' => $completed ? 'completed' : ($hasProof && ! $cancelled ? 'current' : ($cancelled ? 'failed' : 'future')),
            ],
            [
                'label' => $cancelled ? 'Ditolak / dibatalkan' : 'Selesai',
                'state' => $completed ? 'current' : ($cancelled ? 'failed' : 'future'),
            ],
        ];
    }
}
