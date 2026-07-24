<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            'Action' => 'Permainan dengan fokus pada aksi dan respons pemain.',
            'Adventure' => 'Eksplorasi, perjalanan, dan penemuan menjadi bagian utama.',
            'Co-op' => 'Dirancang untuk pengalaman bermain bersama.',
            'Horror' => 'Suasana menegangkan dengan unsur horor.',
            'Open World' => 'Dunia luas yang dapat dijelajahi secara relatif bebas.',
            'Puzzle' => 'Tantangan diselesaikan melalui logika dan pemecahan masalah.',
            'Racing' => 'Balapan dan pengalaman berkendara.',
            'RPG' => 'Perkembangan karakter, pilihan, dan sistem peran.',
            'Simulation' => 'Sistem permainan meniru aktivitas atau dunia tertentu.',
            'Stealth' => 'Pendekatan diam-diam menjadi pilihan penting.',
            'Story Rich' => 'Narasi dan karakter menjadi daya tarik utama.',
            'Survival' => 'Pemain mengelola sumber daya untuk bertahan hidup.',
        ];

        foreach ($genres as $name => $description) {
            Genre::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description],
            );
        }
    }
}

