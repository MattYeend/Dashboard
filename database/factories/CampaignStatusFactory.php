<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CampaignStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<CampaignStatus>
 */
class CampaignStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<CampaignStatus>
     */
    protected $model = CampaignStatus::class;

    /**
     * Define the model's default state.
     *
     * Factories may use Faker as they only run under tests.
     * Seeders must not.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Tenant::current()?->id,
            'title' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
            'background_colour' => $this->faker->hexColor(),
            'text_colour' => $this->faker->hexColor(),
            'system_key' => null,
            'meta' => null,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
            'restored_by' => null,
            'restored_at' => null,
        ];
    }

    /**
     * State for a protected system status.
     */
    public function system(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'system_key' => $key,
        ]);
    }

    /**
     * State for a campaign status with no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * State for a campaign status with meta data.
     *
     * @param  array<string, mixed>  $meta
     */
    public function withMeta(array $meta = []): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => $meta ?: ['key' => $this->faker->word(), 'value' => $this->faker->word()],
        ]);
    }

    /**
     * State for a campaign status with a specific background and text colour.
     */
    public function withColours(string $backgroundColour, string $textColour): static
    {
        return $this->state(fn (array $attributes) => [
            'background_colour' => $backgroundColour,
            'text_colour' => $textColour,
        ]);
    }

    /**
     * State for a soft-deleted campaign status.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }

    /**
     * State for a restored campaign status.
     */
    public function restored(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => null,
            'restored_at' => now(),
            'restored_by' => User::factory(),
        ]);
    }

    /**
     * State for a campaign status created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * State for the "Draft" status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Draft',
            'description' => 'Campaign is being prepared and has not yet been scheduled.',
            'background_colour' => '#e2e8f0',
            'text_colour' => '#1a202c',
            'system_key' => CampaignStatus::KEY_DRAFT,
        ]);
    }

    /**
     * State for the "Scheduled" status.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Scheduled',
            'description' => 'Campaign is ready and will be sent at the chosen date and time.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
            'system_key' => CampaignStatus::KEY_SCHEDULED,
        ]);
    }

    /**
     * State for the "Sending" status.
     */
    public function sending(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Sending',
            'description' => 'Campaign is currently being delivered to its recipients.',
            'background_colour' => '#d6bcfa',
            'text_colour' => '#553c9a',
            'system_key' => CampaignStatus::KEY_SENDING,
        ]);
    }

    /**
     * State for the "Sent" status.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Sent',
            'description' => 'Campaign has been delivered to all of its recipients.',
            'background_colour' => '#c6f6d5',
            'text_colour' => '#22543d',
            'system_key' => CampaignStatus::KEY_SENT,
        ]);
    }

    /**
     * State for the "Paused" status.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Paused',
            'description' => 'Campaign delivery has been paused and can be resumed.',
            'background_colour' => '#fefcbf',
            'text_colour' => '#744210',
            'system_key' => CampaignStatus::KEY_PAUSED,
        ]);
    }

    /**
     * State for the "Cancelled" status.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Cancelled',
            'description' => 'Campaign has been cancelled and will not be sent.',
            'background_colour' => '#e2e8f0',
            'text_colour' => '#718096',
            'system_key' => CampaignStatus::KEY_CANCELLED,
        ]);
    }

    /**
     * State for the "Failed" status.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Failed',
            'description' => 'Campaign could not be delivered due to a sending or processing failure.',
            'background_colour' => '#feb2b2',
            'text_colour' => '#822727',
            'system_key' => CampaignStatus::KEY_FAILED,
        ]);
    }
}
