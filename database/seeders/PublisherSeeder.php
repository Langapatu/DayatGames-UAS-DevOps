<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PublisherSeeder extends Seeder
{
    public function run(): void
    {
        $publishers = [
            '2K',
            '505 Games',
            'Deep Silver',
            'Electronic Arts',
            'Focus Entertainment',
            'KRAFTON',
            'Newnight',
            'PlayStation Publishing',
            'Quantic Dream',
            'Ravenscourt',
            'Rockstar Games',
            'THQ Nordic',
            'Unknown Worlds Entertainment',
            'Warner Bros. Games',
            'Xbox Game Studios',
        ];

        foreach ($publishers as $name) {
            Publisher::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
