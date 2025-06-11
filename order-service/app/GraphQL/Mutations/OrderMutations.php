<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Jobs\UpdateProductStock;

final readonly class OrderMutations
{
    public function create($_, array $args)
    {
        $input = $args['input'];

        $validator = Validator::make($input, [
            'product_id' => 'required',
            'user_id' => 'required',
            'status' => 'required',
            'total_price' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $order = Order::create([
            'id' => Str::uuid(),
            'code' => 'OR-' . Str::random(8),
            'product_id' => $input['product_id'],
            'user_id' => $input['user_id'],
            'status' => $input['status'],
            'total_price' => $input['total_price'],
            'quantity' => $input['quantity'],
        ]);

        // Dispatch asynchronous job to update product stock
        UpdateProductStock::dispatch($input['product_id'], $input['quantity'])
            ->onQueue('product-stock-update');

        return $order;
    }
}
