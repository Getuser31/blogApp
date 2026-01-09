<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Category;
use Illuminate\Support\Collection;

final readonly class DeleteCategory
{
    /** @param array{} $args
     * @throws \Exception
     */
    public function __invoke(null $_, array $args)
    {
        $category =  Category::findOrFail($args['id']);
        if(!$category){
            throw new \Exception('Category not found');
        }

        $category->delete();
        return $category;
    }
}
