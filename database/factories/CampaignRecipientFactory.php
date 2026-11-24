<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<CampaignRecipient>
 */
class CampaignRecipientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<CampaignRecipient>
     */
    protected $model = CampaignRecipient::class;

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
            'campaign_id' => Campaign::factory(),
            'contact_id' => null,
            'email' => $this->faker->unique()->safeEmail(),
            'name' => $this->faker->name(),
            'status' => CampaignRecipient::STATUS_PENDING,
            'token' => Str::random(40),
            'attempts' => 0,
            'sent_at' => null,
            'opened_at' => null,
            'clicked_at' => null,
            'unsubscribed_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
        ];
    }

    /**
     * State for a recipient that has been sent the campaign.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignRecipient::STATUS_SENT,
            'attempts' => 1,
            'sent_at' => now()->subHour(),
        ]);
    }

    /**
     * State for a recipient whose send failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignRecipient::STATUS_FAILED,
            'attempts' => 3,
            'failed_at' => now()->subMinutes(10),
            'failure_reason' => 'Mailbox unavailable',
        ]);
    }

    /**
     * State for a soft-deleted recipient.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
