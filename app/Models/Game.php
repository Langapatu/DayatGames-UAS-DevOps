<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'developer_id',
        'publisher_id',
        'steam_app_id',
        'title',
        'slug',
        'short_description',
        'description',
        'original_price',
        'discount_price',
        'discount_percent',
        'price_checked_at',
        'price_source_url',
        'price_is_demo',
        'release_date',
        'platform',
        'operating_system',
        'cover_image',
        'hero_image',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'discount_percent' => 'integer',
            'price_checked_at' => 'datetime',
            'price_is_demo' => 'boolean',
            'release_date' => 'date',
            'is_featured' => 'boolean',
        ];
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(GameImage::class)->orderBy('sort_order');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function libraries(): HasMany
    {
        return $this->hasMany(Library::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function currentPrice(): string
    {
        return $this->discount_price ?? $this->original_price;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
