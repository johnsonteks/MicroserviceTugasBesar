<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final readonly class UserMutations
{
    /** @param array{} $args */
    public function __invoke(null $_, array $args)
    {
        // Not used
    }

    public function create($root, array $args)
    {
        $input = $args['input'];
        $input['password'] = Hash::make($input['password']);

        return User::create($input);
    }

    public function update($root, array $args)
    {
        $input = $args['input'];
        $user = User::findOrFail($input['id']);

        if (isset($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        }

        $user->update($input);
        return $user;
    }

    public function delete($root, array $args)
    {
        $user = User::findOrFail($args['id']);
        $user->delete();
        return $user;
    }
}
