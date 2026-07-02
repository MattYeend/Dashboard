<?php

namespace Database\Factories;

use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatus>
 */
class OrderStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<OrderStatus>
     */
    protected $model = OrderStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
            'background_colour' => $this->faker->hexColor(),
            'text_colour' => $this->faker->hexColor(),
            'meta' => null,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
            'restored_by' => null,
            'restored_at' => null,
        ];
    }

    /**
     * State for an order status with no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * State for an order status with meta data.
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
     * State for an order status with a specific background and text colour.
     */
    public function withColours(string $backgroundColour, string $textColour): static
    {
        return $this->state(fn (array $attributes) => [
            'background_colour' => $backgroundColour,
            'text_colour' => $textColour,
        ]);
    }

    /**
     * State for a soft-deleted order status.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }

    /**
     * State for a restored order status.
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
     * State for an order status created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * State for the "Pending" status.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Pending',
            'description' => 'Order has been placed but payment has not yet been confirmed.',
            'background_colour' => '#e2e8f0',
            'text_colour' => '#1a202c',
        ]);
    }

    /**
     * State for the "Processing" status.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Processing',
            'description' => 'Payment has been confirmed and the order is being prepared.',
            'background_colour' => '#bee3f8',
            'text_colour' => '#2b6cb0',
        ]);
    }

    /**
     * State for the "Shipped" status.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Shipped',
            'description' => 'Order has left the warehouse and is on its way to the customer.',
            'background_colour' => '#d6bcfa',
            'text_colour' => '#553c9a',
        ]);
    }

    /**
     * State for the "Delivered" status.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Delivered',
            'description' => 'Order has been delivered to the customer.',
            'background_colour' => '#c6f6d5',
            'text_colour' => '#22543d',
        ]);
    }

    /**
     * State for the "On Hold" status.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'On Hold',
            'description' => 'Order has been paused due to a stock, payment, or fulfilment issue.',
            'background_colour' => '#fefcbf',
            'text_colour' => '#744210',
        ]);
    }

    /**
     * State for the "Cancelled" status.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Cancelled',
            'description' => 'Order has been cancelled and will not be fulfilled.',
            'background_colour' => '#e2e8f0',
            'text_colour' => '#718096',
        ]);
    }

    /**
     * State for the "Refunded" status.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Refunded',
            'description' => 'Order has been returned and the customer has been refunded.',
            'background_colour' => '#fed7d7',
            'text_colour' => '#742a2a',
        ]);
    }

    /**
     * State for the "Failed" status.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Failed',
            'description' => 'Order could not be completed due to a payment or processing failure.',
            'background_colour' => '#feb2b2',
            'text_colour' => '#822727',
        ]);
    }
}
