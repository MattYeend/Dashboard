<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Deal;
use App\Models\DealStatus;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Deal>
     */
    protected $model = Deal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expectedClose = $this->faker->dateTimeBetween('now', '+6 months');

        return [
            'title' => $this->faker->catchPhrase(),
            'description' => $this->faker->optional()->paragraph(),
            'pipeline_id' => Pipeline::factory(),
            'stage_id' => PipelineStage::factory(),
            'status_id' => DealStatus::factory(),
            'company_id' => Company::factory(),
            'invoice_id' => null,
            'value' => $this->faker->numberBetween(1000, 500000),
            'currency' => 'GBP',
            'probability' => $this->faker->numberBetween(0, 100),
            'expected_close_date' => $expectedClose,
            'closed_at' => null,
            'meta' => null,
        ];
    }

    /**
     * Indicate that the deal has been won.
     */
    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => DealStatus::where('title', 'Won')->value('id'),
            'probability' => 100,
            'closed_at' => now(),
        ]);
    }

    /**
     * Indicate that the deal has been lost.
     */
    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => DealStatus::where('title', 'Lost')->value('id'),
            'probability' => 0,
            'closed_at' => now(),
        ]);
    }

    /**
     * Indicate that the deal is still open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => DealStatus::where('title', 'Open')->value('id'),
            'closed_at' => null,
        ]);
    }

    /**
     * Associate the deal with a given pipeline stage.
     */
    public function atStage(PipelineStage $stage): static
    {
        return $this->state(fn (array $attributes) => [
            'pipeline_id' => $stage->pipeline_id,
            'stage_id' => $stage->id,
        ]);
    }

    /**
     * Associate the deal with a given company.
     */
    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
        ]);
    }

    /**
     * Indicate that the deal is soft-deleted.
     */
    public function deleted(?User $deletedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => $deletedBy?->id,
        ]);
    }
}
