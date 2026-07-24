<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_manage_genres(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.genres.store'), [
                'name' => 'Turn Based',
                'description' => 'Game dengan giliran.',
            ])
            ->assertRedirect(route('admin.genres.index'));

        $genre = Genre::where('slug', 'turn-based')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.genres.update', $genre), [
                'name' => 'Turn-Based Strategy',
                'description' => 'Strategi berbasis giliran.',
            ])
            ->assertRedirect(route('admin.genres.index'));

        $genre->refresh();
        $this->assertSame('turn-based-strategy', $genre->slug);

        $this->actingAs($this->admin)
            ->delete(route('admin.genres.destroy', $genre))
            ->assertRedirect();

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function test_admin_can_manage_publishers_and_developers(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.publishers.store'), [
                'name' => 'Dayat Publishing',
                'website' => 'https://example.com',
                'description' => 'Publisher pengujian.',
            ])
            ->assertRedirect(route('admin.publishers.index'));

        $publisher = Publisher::where('slug', 'dayat-publishing')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.publishers.update', $publisher), [
                'name' => 'Dayat Interactive',
                'website' => 'https://example.com/publisher',
                'description' => 'Publisher telah diperbarui.',
            ])
            ->assertRedirect(route('admin.publishers.index'));

        $this->assertDatabaseHas('publishers', [
            'id' => $publisher->id,
            'slug' => 'dayat-interactive',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.developers.store'), [
                'name' => 'Dayat Studio',
                'website' => 'https://example.com/studio',
                'description' => 'Developer pengujian.',
            ])
            ->assertRedirect(route('admin.developers.index'));

        $developer = Developer::where('slug', 'dayat-studio')->firstOrFail();

        $this->actingAs($this->admin)
            ->delete(route('admin.developers.destroy', $developer))
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->delete(route('admin.publishers.destroy', $publisher->refresh()))
            ->assertRedirect();

        $this->assertDatabaseMissing('developers', ['id' => $developer->id]);
        $this->assertDatabaseMissing('publishers', ['id' => $publisher->id]);
    }

    public function test_admin_can_create_update_and_delete_a_game_with_genres(): void
    {
        $developer = Developer::create(['name' => 'Studio Test', 'slug' => 'studio-test']);
        $publisher = Publisher::create(['name' => 'Publisher Test', 'slug' => 'publisher-test']);
        $genre = Genre::create(['name' => 'Adventure', 'slug' => 'adventure']);

        $payload = $this->gamePayload($developer, $publisher, $genre);

        $this->actingAs($this->admin)
            ->post(route('admin.games.store'), $payload)
            ->assertSessionHasNoErrors();

        $game = Game::where('slug', 'game-crud')->firstOrFail();
        $this->assertDatabaseHas('game_genre', [
            'game_id' => $game->id,
            'genre_id' => $genre->id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.games.update', $game), [
                ...$payload,
                'title' => 'Game CRUD Deluxe',
                'discount_price' => 50000,
                'discount_percent' => 50,
            ])
            ->assertSessionHasNoErrors();

        $game->refresh();
        $this->assertSame('game-crud-deluxe', $game->slug);
        $this->assertSame('50000.00', $game->discount_price);

        $this->actingAs($this->admin)
            ->delete(route('admin.games.destroy', $game))
            ->assertRedirect(route('admin.games.index'));

        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }

    public function test_game_price_validation_rejects_inconsistent_discount(): void
    {
        $developer = Developer::create(['name' => 'Studio Test', 'slug' => 'studio-test']);
        $publisher = Publisher::create(['name' => 'Publisher Test', 'slug' => 'publisher-test']);
        $genre = Genre::create(['name' => 'Adventure', 'slug' => 'adventure']);

        $this->actingAs($this->admin)
            ->post(route('admin.games.store'), [
                ...$this->gamePayload($developer, $publisher, $genre),
                'discount_price' => 125000,
                'discount_percent' => 25,
            ])
            ->assertSessionHasErrors('discount_price');

        $this->assertDatabaseCount('games', 0);
    }

    private function gamePayload(
        Developer $developer,
        Publisher $publisher,
        Genre $genre,
    ): array {
        return [
            'developer_id' => $developer->id,
            'publisher_id' => $publisher->id,
            'title' => 'Game CRUD',
            'short_description' => 'Deskripsi singkat untuk game pengujian.',
            'description' => 'Deskripsi lengkap untuk game pengujian CRUD admin.',
            'original_price' => 100000,
            'discount_price' => 75000,
            'discount_percent' => 25,
            'price_is_demo' => '1',
            'release_date' => '2026-01-01',
            'platform' => 'PC',
            'operating_system' => 'Windows 11',
            'status' => 'published',
            'is_featured' => '1',
            'genres' => [$genre->id],
        ];
    }
}
