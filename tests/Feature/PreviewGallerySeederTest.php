<?php

namespace Tests\Feature;

use App\Models\Game;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreviewGallerySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_assigns_four_ordered_preview_images_to_every_game_without_duplicates(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $games = Game::query()
            ->published()
            ->with('images')
            ->orderBy('slug')
            ->get();

        $this->assertCount(18, $games);

        foreach ($games as $game) {
            $this->assertCount(4, $game->images, $game->title.' should have four preview images.');
            $this->assertSame([1, 2, 3, 4], $game->images->pluck('sort_order')->all());

            foreach ($game->images as $image) {
                $this->assertSame('gallery', $image->image_type);
                $this->assertFileExists(public_path($image->image_path));
            }
        }
    }
}
