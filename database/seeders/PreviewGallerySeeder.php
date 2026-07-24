<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class PreviewGallerySeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const GAME_SLUGS = [
        'atomic-heart',
        'death-stranding-directors-cut',
        'detroit-become-human',
        'elden-ring',
        'forza-horizon-6',
        'ghost-of-tsushima',
        'ghost-of-yotei',
        'god-of-war',
        'grand-theft-auto-v',
        'hogwarts-legacy',
        'it-takes-two',
        'kingdom-come-deliverance-ii',
        'lego-batman-legacy-of-the-dark-knight',
        'mafia-the-old-country',
        'red-dead-redemption-2',
        'road-96',
        'sons-of-the-forest',
        'subnautica-2',
    ];

    public function run(): void
    {
        $games = Game::query()
            ->whereIn('slug', self::GAME_SLUGS)
            ->get()
            ->keyBy('slug');

        foreach (self::GAME_SLUGS as $slug) {
            $game = $games->get($slug);

            if (! $game) {
                continue;
            }

            foreach (range(1, 4) as $sortOrder) {
                $game->images()->updateOrCreate(
                    ['sort_order' => $sortOrder],
                    [
                        'image_path' => sprintf(
                            'images/previews/%s/%02d.jpg',
                            $slug,
                            $sortOrder,
                        ),
                        'image_type' => 'gallery',
                    ],
                );
            }
        }
    }
}
