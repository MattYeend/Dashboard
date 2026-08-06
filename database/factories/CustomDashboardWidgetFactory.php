<?php

namespace Database\Factories;

use App\Enums\DashboardDateRange;
use App\Models\CustomDashboardWidget;
use App\Models\User;
use App\Support\DashboardMetricRegistry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomDashboardWidget>
 */
class CustomDashboardWidgetFactory extends Factory
{
    protected $model = CustomDashboardWidget::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => $this->faker->words(2, true),
            'description' => $this->faker->optional()->sentence(),
            'metric_key' => $this->faker->randomElement(DashboardMetricRegistry::keys()),
            'date_range' => $this->faker->randomElement(array_column(DashboardDateRange::cases(), 'value')),
            'position' => $this->faker->numberBetween(0, 10),
            'is_visible' => true,
        ];
    }
}
