<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

final readonly class EditArticle
{
    /** @param array{} $args
     * @throws ValidationException
     */
    public function __invoke(null $_, array $args): Article|Collection
    {
        $validator = Validator::make($args, [
            'id' => ['required', 'integer', 'exists:articles,id'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'categoryIds' => ['nullable', 'array'],
            'categoryIds.*' => ['exists:categories,id']
        ]);

        $validator->validate();

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $article = Article::findOrFail($args['id']);

        $user = auth()->user();

        if ($article->author_id !== $user->getId() && !$user->isAdmin()) {
            throw new \Exception('You are not authorized to edit this article.');
        }

        // Update or create the translation for the given locale.
        if (isset($args['title']) || isset($args['content'])) {
            $locale = $args['locale'] ?? 'en';

            $article->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title'   => $args['title'] ?? $article->translations()->where('locale', $locale)->value('title'),
                    'content' => $args['content'] ?? $article->translations()->where('locale', $locale)->value('content'),
                ]
            );
        }

        if (isset($args['categoryIds'])) {
            $article->categories()->sync($args['categoryIds']);
        }

        if (isset($args['images'])) {
            /** @var UploadedFile $imageFile */
            foreach ($args['images'] as $imageFile) {
                if ($imageFile === null) {
                    continue;
                }
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
