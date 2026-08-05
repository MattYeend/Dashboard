<?php

namespace Database\Factories;

use App\Models\DashboardWidgetPreference;
use App\Models\User;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DashboardWidgetPreference>
 */
class DashboardWidgetPreferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<DashboardWidgetPreference>
     */
    protected $model = DashboardWidgetPreference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'widget_key' => $this->faker->randomElement(DashboardWidgetRegistry::keys()),
            'position' => $this->faker->numberBetween(0, 6),
            'is_visible' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the widget is hidden from the dashboard.
     */
    public function hidden(): static
    {
        return $this->state(fn () => ['is_visible' => false]);
    }
}
