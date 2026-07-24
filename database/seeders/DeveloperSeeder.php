<?php

namespace Database\Seeders;

use App\Models\Developer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        $developers = [
            'Avalanche Software',
            'DigixArt',
            'Endnight Games',
            'Hangar 13',
            'Hazelight Studios',
            'Kojima Productions',
            'Mundfish',
            'Playground Games',
            'Quantic Dream',
            'Rockstar North',
            'Rockstar Studios',
            'Santa Monica Studio',
            'Sucker Punch Productions',
            'TT Games',
            'Unknown Worlds Entertainment',
            'Warhorse Studios',
        ];

        foreach ($developers as $name) {
            Developer::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}

