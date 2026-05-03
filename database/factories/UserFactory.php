<?php

namespace Database\Factories;

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
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->firstName() . " " . fake()->lastName(),
             'id_number' => $this->idNumber(),
            'account_number' => $this->accountNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'customer',
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

    protected function accountNumber(): string
    {
        return fake()->unique()->numerify(str_repeat('#', 16));
    }

    protected function employeeId(): string
    {
        return fake()->unique()->numerify(str_repeat('#', 12));
    }

    protected function idNumber(): string
    {
        return fake()->unique()->numerify('#############');
    }
}
