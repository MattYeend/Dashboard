<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Order>
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 5000);
        $discount = $this->faker->randomFloat(2, 0, $subtotal * 0.1);
        $tax = round(($subtotal - $discount) * 0.2, 2);
        $total = round($subtotal - $discount + $tax, 2);

        return [
            'orderable_type' => null,
            'orderable_id' => null,
            'order_number' => 'ORD-'.strtoupper($this->faker->unique()->bothify('####-????')),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'notes' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'ordered_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'due_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'completed_at' => null,
            'status_id' => null,
            'meta' => null,
        ];
    }

    /**
     * State for a completed order.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
        ]);
    }

    /**
     * State for an order with no due date.
     */
    public function withoutDueDate(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => null,
        ]);
    }

    /**
     * State for a soft-deleted order.
     */
    public function deleted(?User $deletedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => $deletedBy?->id,
        ]);
    }

    /**
     * Associate the order with a given morphable model.
     */
    public function forModel(Model $model): static
    {
        return $this->state(fn (array $attributes) => [
            'orderable_type' => $model->getMorphClass(),
            'orderable_id' => $model->getKey(),
        ]);
    }

    /**
     * Associate the order with a given status.
     */
    public function forStatus(OrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => $status->id,
        ]);
    }
}
