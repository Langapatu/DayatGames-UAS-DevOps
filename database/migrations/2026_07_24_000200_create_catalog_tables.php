<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('developer_id')->constrained()->restrictOnDelete();
            $table->foreignId('publisher_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('steam_app_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description');
            $table->longText('description');
            $table->decimal('original_price', 15, 2);
            $table->decimal('discount_price', 15, 2)->nullable();
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->timestamp('price_checked_at')->nullable();
            $table->string('price_source_url')->nullable();
            $table->boolean('price_is_demo')->default(true);
            $table->date('release_date')->nullable();
            $table->string('platform');
            $table->string('operating_system')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('hero_image')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
            $table->index(['status', 'release_date']);
        });

        Schema::create('game_genre', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'genre_id']);
        });

        Schema::create('game_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('image_type', 30)->default('gallery');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['game_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_images');
        Schema::dropIfExists('game_genre');
        Schema::dropIfExists('games');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('developers');
    }
};

