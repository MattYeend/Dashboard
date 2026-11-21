<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @extends Factory<EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<EmailTemplate>
     */
    protected $model = EmailTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Tenant::current()?->id,
            'name' => $this->faker->words(3, true),
            'subject' => $this->faker->words(3, true),
            'preheader' => $this->faker->words(3, true),
            'body_html' => '<p>'.$this->faker->paragraph().'</p>',
            'body_text' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the email template is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the email template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the email template is soft-deleted.
     */
    public function deleted(?User $deletedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => $deletedBy?->id,
        ]);
    }
}
