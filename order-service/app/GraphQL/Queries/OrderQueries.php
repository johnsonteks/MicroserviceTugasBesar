<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Order;

final readonly class OrderQueries
{
    public function all($_, array $args)
    {
        return Order::all();
    }

    public function find($_, array $args)
    {
        return Order::findOrFail($args['id']);
    }

    public function byUser($_, array $args)
    {
        return Order::where('user_id', $args['id'])->get();
    }
}
