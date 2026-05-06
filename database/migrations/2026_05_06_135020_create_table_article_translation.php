<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->string('locale');
            $table->string('title');
            $table->text('content');
            $table->unique(['article_id', 'locale']);
            $table->timestamps();
        });

        DB::table('articles')->orderBy('id')->each(function ($article) {
            DB::table('article_translations')->insert([
                'article_id' => $article->id,
                'locale'     => 'en',
                'title'      => $article->title,
                'content'    => $article->content,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['title', 'content']);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title')->after('id');
            $table->text('content')->after('title');
        });

        DB::table('article_translations')->where('locale', 'en')->orderBy('id')->each(function ($translation) {
            DB::table('articles')->where('id', $translation->article_id)->update([
                'title'   => $translation->title,
                'content' => $translation->content,
            ]);
        });

        Schema::dropIfExists('article_translations');
    }
};