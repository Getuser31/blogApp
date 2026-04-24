<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comments newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comments newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comments query()
 * @mixin \Eloquent
 */
class Comments extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'article_id'
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
