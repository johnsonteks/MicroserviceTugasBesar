<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Product;
use Illuminate\Support\Facades\Validator;

final readonly class ProductMutations
{
    public function create($_, array $args)
    {
        return Product::create($args['input']);
    }

    public function update($_, array $args)
    {
        $product = Product::findOrFail($args['input']['id']);
        $product->update($args['input']);
        return $product->fresh();
    }

    public function delete($_, array $args)
    {
        $product = Product::findOrFail($args['id']);
        $product->delete();
        return $product;
    }

    public function increaseStock($_, array $args)
    {
        $input = $args['input'];
        $validator = Validator::make($input, [
            'product_quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $product = Product::findOrFail($input['id']);
        $product->stock += $input['product_quantity'];
        $product->save();

        return $product;
    }

    public function decreaseStock($_, array $args)
    {
        $input = $args['input'];
        $validator = Validator::make($input, [
            'product_quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $product = Product::findOrFail($input['id']);

        if ($product->stock < $input['product_quantity']) {
            throw new \Exception('Stock tidak mencukupi');
        }

        $product->stock -= $input['product_quantity'];
        $product->save();

        return $product;
    }
}
