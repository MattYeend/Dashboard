<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CampaignSegment;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignSegment>
 */
class CampaignSegmentFactory extends Factory
{
    protected $model = CampaignSegment::class;

    /**
     * Define the model's default state.
     *
     * Factories may use Faker because they only run under tests.
     * Seeders must not.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::current()?->id
                ?? Organisation::query()->oldest('id')->value('id'),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'criteria' => [
                ['field' => 'email_subscribed', 'operator' => 'equals', 'value' => true],
            ],
            'estimated_recipients_count' => fake()->numberBetween(0, 500),
            'last_refreshed_at' => null,
        ];
    }

    /**
     * Indicate that the segment is soft deleted.
     */
    public function deleted(): static
    {
        return $this->state(['deleted_at' => now()]);
    }
}
