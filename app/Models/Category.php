<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function articles(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_category', 'category_id', 'article_id');
    }
}
