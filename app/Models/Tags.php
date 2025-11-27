<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tags newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tags newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tags query()
 * @mixin \Eloquent
 */
class Tags extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];
}
