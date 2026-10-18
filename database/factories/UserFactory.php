<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
            'name' => fake()->name(),
            'user_name' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'account_type' => User::TYPE_CLIENT,
            'status' => 1,
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

    /**
     * Indicate that the user is a freelancer.
     */
    public function freelancer(): static
    {
        // Approved with a confirmed email, like most freelancers in the tests; use pendingFreelancer() for new ones.
        return $this->state(fn (array $attributes) => [
            'account_type' => User::TYPE_FREELANCER,
            'whatsapp' => '+201000000000',
        ])->afterCreating(fn (User $user) => $user->forceFill([
            'freelancer_status' => User::FREELANCER_APPROVED,
            'email_verified_at' => now(),
        ])->save());
    }

    public function pendingFreelancer(): static
    {
        // Just registered: email not confirmed yet, not verified by the admin.
        return $this->state(fn (array $attributes) => [
            'account_type' => User::TYPE_FREELANCER,
            'whatsapp' => '+201000000000',
            'email_verified_at' => null,
        ])->afterCreating(fn (User $user) => $user->forceFill(['freelancer_status' => User::FREELANCER_PENDING])->save());
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'admin',
        ]);
    }

    /**
     * Indicate that the user account is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
