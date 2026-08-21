<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KbArticle extends Model
{
    use HasFactory;

    protected $table = 'kb_articles';

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'content',
        'views_count',
        'is_published',
    ];

    protected $casts = [
        'views_count' => 'integer',
        'is_published' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(KbTag::class, 'kb_article_tag', 'article_id', 'tag_id');
    }
}
