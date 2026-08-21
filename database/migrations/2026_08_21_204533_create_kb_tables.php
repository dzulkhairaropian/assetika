<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kb_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kb_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('kb_categories')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->integer('views_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('kb_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('kb_article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('kb_articles')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('kb_tags')->cascadeOnDelete();
            $table->primary(['article_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_article_tag');
        Schema::dropIfExists('kb_tags');
        Schema::dropIfExists('kb_articles');
        Schema::dropIfExists('kb_categories');
    }
};
