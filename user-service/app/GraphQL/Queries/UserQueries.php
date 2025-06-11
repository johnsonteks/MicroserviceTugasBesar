<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\User;

final readonly class UserQueries
{
    /** @param array{} $args */
    public function __invoke(null $_, array $args)
    {
        // Not used
    }

    public function all($root, array $args)
    {
        return User::all();
    }

    public function find($root, array $args)
    {
        return User::where($args)->first();
    }
}
