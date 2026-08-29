<?php

namespace Database\Factories;

use App\Models\NotificationBroadcast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationBroadcast>
 */
class NotificationBroadcastFactory extends Factory
{
    protected $model = NotificationBroadcast::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),
            'audience_type' => 'all',
            'audience_ids' => null,
            'sent_at' => null,
            'sent_by' => null,
            'meta' => null,
        ];
    }

    /**
     * Mark the broadcast as already having been sent.
     */
    public function sent(): static
    {
        return $this->state(fn () => ['sent_at' => now()]);
    }

    /**
     * Soft-deleted state, for testing trashed/restore flows.
     */
    public function deleted(): static
    {
        return $this->state(fn () => ['deleted_at' => now()]);
    }
}
