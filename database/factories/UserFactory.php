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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => 'user',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'meta' => json_encode([
                'department' => fake()->randomElement(['General', 'Operations', 'Content', 'Support', 'Analytics']),
                'position' => fake()->jobTitle(),
                'phone' => fake()->phoneNumber(),
            ]),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
            'restored_by' => null,
            'restored_at' => null,
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
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the model has a user role.
     */
    public function normalUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
        ]);
    }

    /**
     * Indicate that the model has an admin role.
     */
    public function adminUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the model has a super admin role.
     */
    public function superAdminUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
        ]);
    }

    /**
     * Indicate that the model was created by a given user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }

    /**
     * Indicate that the model was soft deleted by a given user.
     */
    public function deletedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_by' => $user->id,
            'deleted_at' => now(),
        ]);
    }

    /**
     * Indicate that the model was restored by a given user.
     */
    public function restoredBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'restored_by' => $user->id,
            'restored_at' => now(),
        ]);
    }
}