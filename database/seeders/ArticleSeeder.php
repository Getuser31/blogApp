<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Article::create([
            'title' => 'First Article',
            'content' => 'This is the content of the first article.',
            'author' => 'Admin',
        ]);
    }
}
