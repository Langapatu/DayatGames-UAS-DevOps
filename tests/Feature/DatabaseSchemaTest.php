<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_business_tables_and_important_columns_exist(): void
    {
        $tables = [
            'users',
            'developers',
            'publishers',
            'genres',
            'games',
            'game_genre',
            'game_images',
            'carts',
            'cart_items',
            'wishlists',
            'orders',
            'order_items',
            'payments',
            'vouchers',
            'libraries',
            'reviews',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing business table: {$table}");
        }

        $this->assertTrue(Schema::hasColumns('users', ['role', 'phone', 'avatar']));
        $this->assertTrue(Schema::hasColumns('games', [
            'developer_id',
            'publisher_id',
            'original_price',
            'discount_price',
            'price_is_demo',
            'status',
        ]));
        $this->assertTrue(Schema::hasColumns('order_items', [
            'game_title',
            'unit_price',
            'discount_amount',
            'subtotal',
        ]));
        $this->assertTrue(Schema::hasColumns('vouchers', [
            'code',
            'discount_percent',
            'expires_at',
            'is_active',
        ]));
        $this->assertTrue(Schema::hasColumns('orders', [
            'checkout_token',
            'subtotal_amount',
            'voucher_id',
            'voucher_code',
            'voucher_discount_amount',
            'payment_due_at',
        ]));
    }

    public function test_catalog_models_persist_and_resolve_relationships(): void
    {
        $developer = Developer::create(['name' => 'Studio Test', 'slug' => 'studio-test']);
        $publisher = Publisher::create(['name' => 'Publisher Test', 'slug' => 'publisher-test']);
        $genre = Genre::create(['name' => 'Action', 'slug' => 'action']);
        $user = User::factory()->create(['role' => 'customer']);

        $game = Game::create([
            'developer_id' => $developer->id,
            'publisher_id' => $publisher->id,
            'title' => 'Game Test',
            'slug' => 'game-test',
            'short_description' => 'Deskripsi singkat.',
            'description' => 'Deskripsi panjang.',
            'original_price' => 100000,
            'discount_price' => 75000,
            'discount_percent' => 25,
            'price_is_demo' => true,
            'platform' => 'PC',
            'status' => 'published',
        ]);

        $game->genres()->attach($genre);
        $user->cart()->create();

        $this->assertTrue($game->developer->is($developer));
        $this->assertTrue($game->publisher->is($publisher));
        $this->assertTrue($game->genres->first()->is($genre));
        $this->assertSame('75000.00', $game->currentPrice());
        $this->assertNotNull($user->cart);
    }
}
