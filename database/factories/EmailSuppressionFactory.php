<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EmailSuppression;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<EmailSuppression>
 */
class EmailSuppressionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<EmailSuppression>
     */
    protected $model = EmailSuppression::class;

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
            'email' => strtolower($this->faker->unique()->safeEmail()),
            'reason' => EmailSuppression::REASON_UNSUBSCRIBED,
            'campaign_id' => null,
            'contact_id' => null,
            'suppressed_at' => now(),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * State for an address that hard bounced.
     */
    public function bounced(): static
    {
        return $this->state(fn (array $attributes) => [
            'reason' => EmailSuppression::REASON_BOUNCED,
        ]);
    }

    /**
     * State for an address that reported a message as spam.
     */
    public function complained(): static
    {
        return $this->state(fn (array $attributes) => [
            'reason' => EmailSuppression::REASON_COMPLAINED,
        ]);
    }

    /**
     * State for an address added by hand.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'reason' => EmailSuppression::REASON_MANUAL,
        ]);
    }

    /**
     * State for a soft-deleted email suppression.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
