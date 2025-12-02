<?php

namespace App\GraphQL\Mutations;

use App\Models\Article;
use App\Models\User;
use App\GraphQL\Traits\ValidatesArticleCreation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final readonly class CreateArticle
{
    use ValidatesArticleCreation;

    /**
     * Creates a new article.
     *
     * @param null $_
     * @param array{title: string, content: string, categoryIds?: array<int>, images?: array<UploadedFile>} $args
     * @return Model
     * @throws ValidationException
     */
    public function __invoke(null $_, array $args): Model
    {
        // Use Laravel's Validator facade to validate the arguments.
        $validator = Validator::make($args, $this->rules());
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $validated = $validator->validated();

        /** @var User $user */
        $user = Auth::user();

        /** @var Article $article */
        $article = $user->articles()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        if (isset($validated['categoryIds'])) {
            $article->categories()->attach($validated['categoryIds']);
        }

        if (isset($validated['images'])) {
            /** @var UploadedFile $imageFile */
            foreach ($validated['images'] as $imageFile) {
                // Store the file in the 'article_images' directory on the 'public' disk and get the path.
                $path = Storage::disk('public')->put('article_images', $imageFile);
                // Get the full public URL for the locally stored file.
                $url = Storage::disk('public')->url($path);
                $article->images()->create(['path' => $url]);
            }
        }


        return $article;
    }
}
