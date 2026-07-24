<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CustomerSeeder::class,
            DeveloperSeeder::class,
            PublisherSeeder::class,
            GenreSeeder::class,
            GameSeeder::class,
            PreviewGallerySeeder::class,
        ]);
    }
}
