<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Product;

final readonly class ProductQueries
{
    public function all($_, array $args)
    {
        return Product::all();
    }

    public function find($_, array $args)
    {
        return Product::findOrFail($args['id']);
    }
}
