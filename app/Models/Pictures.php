<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pictures newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pictures newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pictures query()
 * @mixin \Eloquent
 */
class Pictures extends Model
{
    protected $fillable = [
        'name',
        'path',
    ];
}
