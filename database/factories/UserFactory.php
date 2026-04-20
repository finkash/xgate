<?php

namespace Database\Factories;

// App\Models\User - that this factory will be generating instances of.
// Illuminate\Database\Eloquent\Factories\Factory - base class for all model factories in Laravel.
// Illuminate\Support\Facades\Hash - provides methods for hashing passwords.
// Illuminate\Support\Str - provides string manipulation methods, such as generating random strings.
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * This property is used to store the current password being used by the factory. 
     * By making it static, it allows the password to be shared across all instances of the factory,
     * ensuring that the same password is used for all generated users unless explicitly changed.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     * It returns an array of attributes and their corresponding values 
     * that will be used to create a new instance of the model when the factory is invoked.
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
