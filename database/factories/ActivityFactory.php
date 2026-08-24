<?php

namespace Database\Factories;

use App\Enums\ActivityType;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * @var class-string<Activity>
     */
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'activityable_type' => null,
            'activityable_id' => null,
            'type' => $this->faker->randomElement(ActivityType::values()),
            'description' => $this->faker->sentence(),
            'meta' => null,
            'occurred_at' => now(),
        ];
    }

    /**
     * State for a soft-deleted activity.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * Associate the activity with a given morphable model.
     */
    public function forModel(Model $model): static
    {
        return $this->state(fn (array $attributes) => [
            'activityable_type' => $model->getMorphClass(),
            'activityable_id' => $model->getKey(),
        ]);
    }
}
