<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password-uji-123',
            'role' => UserRole::Kasir,
            'is_active' => true,
        ];
    }

    public function role(UserRole $role): static
    {
        return $this->state(['role' => $role]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
