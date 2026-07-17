<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class BlogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Post>
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'description' => '<p>' . implode('</p><p>', $this->faker->paragraphs(4)) . '</p>',
            'image' => null,
            'meta' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * State for a post with an attached image path.
     */
    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => 'blogs/' . $this->faker->uuid() . '.jpg',
        ]);
    }

    /**
     * State for a soft-deleted post.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * Associate the post with a specific author.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}
