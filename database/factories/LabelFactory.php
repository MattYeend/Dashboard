<?php

namespace Database\Factories;

use App\Models\Label;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Label>
 */
class LabelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Label>
     */
    protected $model = Label::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->word());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'background_colour' => $this->faker->hexColor(),
            'text_colour' => '#ffffff',
            'meta' => null,
        ];
    }

    /**
     * State for a label with specific background and text colours.
     */
    public function withColours(string $backgroundColour, string $textColour): static
    {
        return $this->state(fn (array $attributes) => [
            'background_colour' => $backgroundColour,
            'text_colour' => $textColour,
        ]);
    }

    /**
     * State for a soft-deleted label.
     */
    public function deleted(?User $deletedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => $deletedBy?->id,
        ]);
    }
}
