<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comments newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comments newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comments query()
 * @mixin \Eloquent
 */
class Comments extends Model
{
    protected $fillable = [
        'content',
    ];
}
