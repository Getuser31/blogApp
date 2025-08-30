<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Article extends Model
{
    protected $connection = 'mongodb';
    
    protected $fillable = [
        'title',
        'content',
        'author',
    ];

}
