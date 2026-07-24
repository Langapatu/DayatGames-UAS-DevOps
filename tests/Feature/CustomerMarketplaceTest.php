<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Library;
use App\Models\Order;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private Developer $developer;

    private Publisher $publisher;

    private Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->developer = Developer::create(['name' => 'Studio Test', 'slug' => 'studio-test']);
        $this->publisher = Publisher::create(['name' => 'Publisher Test', 'slug' => 'publisher-test']);
        $this->genre = Genre::create(['name' => 'Adventure', 'slug' => 'adventure']);
    }

    public function test_guest_only_sees_published_games_in_catalog(): void
    {
        $published = $this->createGame('Published Game', 'published-game', 'published');
        $draft = $this->createGame('Hidden Draft', 'hidden-draft', 'draft');

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title);

        $this->get(route('catalog.show', $published))
            ->assertOk()
            ->assertSee($published->title);

        $this->get('/games/'.$draft->slug)->assertNotFound();
    }

    public function test_guest_can_search_and_filter_catalog(): void
    {
        $matching = $this->createGame('Space Adventure', 'space-adventure');
        $matching->genres()->attach($this->genre);
        $this->createGame('Racing Legend', 'racing-legend');

        $this->get(route('catalog.index', ['search' => 'Space']))
            ->assertOk()
            ->assertSee($matching->title);
        $this->get(route('catalog.index', ['genre' => $this->genre->slug]))
            ->assertOk()
            ->assertSee($matching->title);
        $this->get(route('catalog.index', ['min_price' => 50000, 'max_price' => 80000]))
            ->assertOk()
            ->assertSee($matching->title);

        $this->get(route('catalog.index', [
            'search' => 'Space',
            'genre' => $this->genre->slug,
            'min_price' => 50000,
            'max_price' => 80000,
        ]))
            ->assertOk()
            ->assertSee($matching->title)
            ->assertDontSee('Racing Legend');
    }

    public function test_customer_can_add_and_remove_wishlist_without_duplicates(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $game = $this->createGame('Wishlist Game', 'wishlist-game');

        $this->actingAs($customer)->post(route('wishlist.store', $game))->assertRedirect();
        $this->actingAs($customer)->post(route('wishlist.store', $game))->assertRedirect();

        $this->assertDatabaseCount('wishlists', 1);

        $this->actingAs($customer)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee($game->title);

        $this->actingAs($customer)->delete(route('wishlist.destroy', $game))->assertRedirect();
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_customer_can_add_and_remove_cart_item_without_duplicates(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $game = $this->createGame('Cart Game', 'cart-game');

        $this->actingAs($customer)->post(route('cart.store', $game))->assertRedirect();
        $this->actingAs($customer)->post(route('cart.store', $game))->assertRedirect();

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', [
            'game_id' => $game->id,
            'price' => '75000.00',
        ]);

        $this->actingAs($customer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee($game->title)
            ->assertSee('Cart, 1 game', false);

        $this->actingAs($customer)->delete(route('cart.destroy', $game))->assertRedirect();
        $this->assertDatabaseCount('cart_items', 0);

        $this->actingAs($customer)
            ->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Cart, 0 game', false);
    }

    public function test_customer_cannot_add_owned_game_to_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $game = $this->createGame('Owned Game', 'owned-game');
        $order = Order::create([
            'user_id' => $customer->id,
            'order_code' => 'DG-OWNED-TEST',
            'total_amount' => 75000,
            'status' => 'completed',
            'ordered_at' => now(),
        ]);
        Library::create([
            'user_id' => $customer->id,
            'game_id' => $game->id,
            'order_id' => $order->id,
            'purchased_at' => now(),
        ]);

        $this->actingAs($customer)
            ->post(route('cart.store', $game))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cart_items', 0);
    }

    private function createGame(
        string $title,
        string $slug,
        string $status = 'published',
    ): Game {
        return Game::create([
            'developer_id' => $this->developer->id,
            'publisher_id' => $this->publisher->id,
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'Deskripsi singkat '.$title,
            'description' => 'Deskripsi lengkap '.$title,
            'original_price' => 100000,
            'discount_price' => 75000,
            'discount_percent' => 25,
            'price_is_demo' => true,
            'release_date' => '2026-01-01',
            'platform' => 'PC',
            'operating_system' => 'Windows',
            'status' => $status,
            'is_featured' => false,
        ]);
    }
}
