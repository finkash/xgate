<?php

namespace App\Domain\IdentityAndAccess\Actions;

use App\Domain\IdentityAndAccess\DTOs\RegisterUserDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUserAction
{
    public function execute(RegisterUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto): User {
            $user = User::create([
                'name' => $dto->username,
                'username' => $dto->username,
                'email' => $dto->email,
                'password' => $dto->password,
            ]);

            $user->profile()->create();

            return $user;
        });
    }
}
