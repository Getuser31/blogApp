<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int $level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Roles extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'level'
    ];
}
