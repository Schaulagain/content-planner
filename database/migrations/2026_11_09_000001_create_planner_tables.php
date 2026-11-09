<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->string('place');
            $table->string('caption')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('series_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage')->default('idea')->index();
            $table->string('photo_path')->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_id')->unique()->constrained()->cascadeOnDelete();
            $table->dateTime('posted_at')->index();
            $table->string('caption');
            $table->unsignedInteger('series_number')->nullable();
            $table->dateTime('reminded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->unsignedInteger('views')->nullable();
            $table->unsignedInteger('likes')->nullable();
            $table->unsignedInteger('comments')->nullable();
            $table->unsignedInteger('shares')->nullable();
            $table->unsignedInteger('saves')->nullable();
            $table->decimal('average_watch_seconds', 6, 2)->nullable();
            $table->dateTime('recorded_at')->nullable();
            $table->timestamps();
            $table->unique(['post_id', 'platform']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->date('week_start')->unique();
            $table->text('learning');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('stats');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('ideas');
        Schema::dropIfExists('series');
    }
};
