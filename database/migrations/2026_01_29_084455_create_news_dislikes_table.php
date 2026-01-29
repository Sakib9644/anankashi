<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_dislikes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('news_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            // One dislike per user per news
            $table->unique(['user_id', 'news_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_dislikes');
    }
};
