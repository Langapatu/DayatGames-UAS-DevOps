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

    public function test_admin_can_create_game_with_inline_developer_and_publisher(): void
    {
        $genre = Genre::create(['name' => 'Soulslike', 'slug' => 'soulslike']);

        $this->actingAs($this->admin)
            ->post(route('admin.games.store'), [
                ...$this->inlineGamePayload($genre),
                'new_developer_name' => '  New Studio  ',
                'new_publisher_name' => 'New Publisher',
            ])
            ->assertSessionHasNoErrors();

        $developer = Developer::where('slug', 'new-studio')->firstOrFail();
        $publisher = Publisher::where('slug', 'new-publisher')->firstOrFail();
        $this->assertSame('New Studio', $developer->name);
        $this->assertSame('New Publisher', $publisher->name);
        $this->assertDatabaseHas('games', [
            'slug' => 'inline-game',
            'developer_id' => $developer->id,
            'publisher_id' => $publisher->id,
        ]);
    }

    public function test_inline_metadata_reuses_existing_normalized_name(): void
    {
        $genre = Genre::create(['name' => 'RPG', 'slug' => 'rpg']);
        Developer::create(['name' => 'Existing Studio', 'slug' => 'existing-studio']);

        $this->actingAs($this->admin)
            ->post(route('admin.games.store'), [
                ...$this->inlineGamePayload($genre),
                'new_developer_name' => ' Existing Studio ',
                'new_publisher_name' => 'Only Publisher',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Developer::where('slug', 'existing-studio')->count());
        $this->assertSame(1, Publisher::where('slug', 'only-publisher')->count());
    }

    public function test_admin_sidebar_does_not_show_standalone_publisher_or_developer_tabs(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.publishers.index'), false)
            ->assertDontSee(route('admin.developers.index'), false)
            ->assertSee(route('admin.games.index'), false);
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

    private function inlineGamePayload(Genre $genre): array
    {
        return [
            'title' => 'Inline Game',
            'short_description' => 'Deskripsi singkat untuk inline metadata.',
            'description' => 'Deskripsi lengkap untuk inline metadata game.',
            'original_price' => 150000,
            'discount_price' => null,
            'discount_percent' => 0,
            'price_is_demo' => '1',
            'release_date' => '2026-02-01',
            'platform' => 'PC',
            'operating_system' => 'Windows',
            'status' => 'published',
            'is_featured' => '0',
            'genres' => [$genre->id],
        ];
    }
}
