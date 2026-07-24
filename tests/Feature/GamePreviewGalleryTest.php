<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\GameImage;
use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamePreviewGalleryTest extends TestCase
{
    use RefreshDatabase;

    private Developer $developer;

    private Publisher $publisher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->developer = Developer::create(['name' => 'Preview Studio', 'slug' => 'preview-studio']);
        $this->publisher = Publisher::create(['name' => 'Preview Publisher', 'slug' => 'preview-publisher']);
    }

    public function test_game_detail_renders_ordered_preview_gallery_and_accessible_lightbox_controls(): void
    {
        $game = $this->createGame('Gallery Game', 'gallery-game');

        foreach ([3, 1, 4, 2] as $order) {
            GameImage::create([
                'game_id' => $game->id,
                'image_path' => "images/previews/gallery-game/0{$order}.jpg",
                'image_type' => 'gallery',
                'sort_order' => $order,
            ]);
        }

        $response = $this->get(route('catalog.show', $game))
            ->assertOk()
            ->assertSee('data-preview-gallery', false)
            ->assertSee('data-preview-lightbox', false)
            ->assertSee('aria-label="Screenshot sebelumnya"', false)
            ->assertSee('aria-label="Screenshot berikutnya"', false)
            ->assertSee('aria-label="Tutup tampilan screenshot"', false)
            ->assertSeeInOrder([
                'images/previews/gallery-game/01.jpg',
                'images/previews/gallery-game/02.jpg',
                'images/previews/gallery-game/03.jpg',
                'images/previews/gallery-game/04.jpg',
            ], false);

        $this->assertSame(4, substr_count($response->getContent(), 'data-preview-slide'));
    }

    public function test_game_detail_uses_hero_image_as_gallery_fallback(): void
    {
        $game = $this->createGame('Fallback Game', 'fallback-game', 'images/games/fallback-game.webp');

        $response = $this->get(route('catalog.show', $game))
            ->assertOk()
            ->assertSee('data-preview-gallery', false)
            ->assertSee('images/games/fallback-game.webp', false);

        $this->assertSame(1, substr_count($response->getContent(), 'data-preview-slide'));
    }

    private function createGame(string $title, string $slug, ?string $heroImage = null): Game
    {
        return Game::create([
            'developer_id' => $this->developer->id,
            'publisher_id' => $this->publisher->id,
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'Deskripsi singkat '.$title,
            'description' => 'Deskripsi lengkap '.$title,
            'original_price' => 100000,
            'discount_price' => null,
            'discount_percent' => 0,
            'price_is_demo' => true,
            'release_date' => '2026-01-01',
            'platform' => 'PC',
            'operating_system' => 'Windows',
            'hero_image' => $heroImage,
            'status' => 'published',
            'is_featured' => false,
        ]);
    }
}
