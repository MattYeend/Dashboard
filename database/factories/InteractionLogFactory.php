<?php

namespace Database\Factories;

use App\Enums\InteractionLogType;
use App\Models\Contact;
use App\Models\InteractionLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<InteractionLog>
 */
class InteractionLogFactory extends Factory
{
    /**
     * @var class-string<InteractionLog>
     */
    protected $model = InteractionLog::class;

    public function definition(): array
    {
        return [
            'interactable_type' => null,
            'interactable_id' => null,
            'type' => $this->faker->randomElement(InteractionLogType::values()),
            'subject' => $this->faker->sentence(4),
            'outcome' => $this->faker->sentence(),
            'notes' => $this->faker->optional()->paragraph(),
            'occurred_at' => now(),
            'contact_id' => null,
        ];
    }

    /**
     * State for a soft-deleted interaction log.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * Associate the interaction log with a given morphable model.
     */
    public function forModel(Model $model): static
    {
        return $this->state(fn (array $attributes) => [
            'interactable_type' => $model->getMorphClass(),
            'interactable_id' => $model->getKey(),
        ]);
    }

    /**
     * Associate the interaction log with a contact.
     */
    public function withContact(Contact $contact): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_id' => $contact->id,
        ]);
    }

    /**
     * State for a call interaction.
     */
    public function call(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InteractionLogType::Call,
        ]);
    }

    /**
     * State for an email interaction.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InteractionLogType::Email,
        ]);
    }
}
