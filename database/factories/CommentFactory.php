<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Comment>
     */
    protected $model = Comment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'commentable_type' => null,
            'commentable_id' => null,
            'content' => $this->faker->paragraph(),
            'meta' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Associate the comment with a given morphable model.
     */
    public function forModel(Model $model): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => $model->getMorphClass(),
            'commentable_id' => $model->getKey(),
        ]);
    }

    /**
     * Indicate that the comment has been soft-deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::factory(),
        ]);
    }
}
