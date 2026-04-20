<?php

namespace App\Domain\IdentityAndAccess\DTOs;

class RegisterUserDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $password
    ) {
    }

    /**
     * @param array{username:string,email:string,password:string} $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            username: $validated['username'],
            email: $validated['email'],
            password: $validated['password']
        );
    }
}
