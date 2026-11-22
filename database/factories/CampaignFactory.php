<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Campaign>
     */
    protected $model = Campaign::class;

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
            'name' => $this->faker->words(3, true),
            'email_template_id' => null,
            'campaign_segment_id' => null,
            'owner_id' => null,
            'subject' => $this->faker->sentence(4),
            'from_name' => $this->faker->name(),
            'reply_to' => $this->faker->safeEmail(),
            'scheduled_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'rendered_subject' => $this->faker->sentence(4),
            'rendered_body_html' => '<p>'.$this->faker->paragraph().'</p>',
            'rendered_body_text' => $this->faker->paragraph(),
            'recipients_count' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'skipped_count' => 0,
            'opened_count' => 0,
            'unsubscribed_count' => 0,
        ];
    }

    /**
     * State for a campaign owned by a specific user.
     */
    public function forOwner(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $user->id,
        ]);
    }

    /**
     * State for a campaign scheduled to send in the future.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->addDay(),
            'recipients_count' => 100,
        ]);
    }

    /**
     * State for a campaign that is part way through sending.
     */
    public function sending(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->subHour(),
            'started_at' => now()->subMinutes(10),
            'recipients_count' => 100,
            'sent_count' => 40,
        ]);
    }

    /**
     * State for a campaign that has finished sending.
     *
     * The counts add up: 94 sent, 2 failed and 4 skipped make 100 recipients.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->subDay(),
            'started_at' => now()->subDay(),
            'completed_at' => now()->subDay()->addMinutes(15),
            'recipients_count' => 100,
            'sent_count' => 94,
            'failed_count' => 2,
            'skipped_count' => 4,
            'opened_count' => 41,
            'unsubscribed_count' => 1,
        ]);
    }

    /**
     * State for a cancelled campaign.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->addDay(),
            'cancelled_at' => now(),
        ]);
    }

    /**
     * State for a soft-deleted campaign.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
