<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Ticket>
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(),
            'ticket_status_id' => TicketStatus::factory(),
            'ticket_priority_id' => TicketPriority::factory(),
            'assigned_to' => User::factory(),
            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'resolved_at' => null,
            'meta' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * State for a resolved ticket.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolved_at' => now(),
        ]);
    }

    /**
     * State for an overdue ticket.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->subDays(3),
            'resolved_at' => null,
        ]);
    }

    /**
     * State for an unassigned ticket.
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => null,
        ]);
    }

    /**
     * Assign the ticket to a specific user.
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }

    /**
     * Set a specific status on the ticket.
     */
    public function withStatus(TicketStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'ticket_status_id' => $status->id,
        ]);
    }

    /**
     * Set a specific priority on the ticket.
     */
    public function withPriority(TicketPriority $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'ticket_priority_id' => $priority->id,
        ]);
    }

    /**
     * State for a soft-deleted ticket.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
