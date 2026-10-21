<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Like;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Like>
 */
class LikeFactory extends Factory
{
    /**
     * @var class-string<Like>
     */
    protected $model = Like::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'likeable_type' => null,
            'likeable_id' => null,
        ];
    }

    /**
     * Associate the like with a given likeable model.
     */
    public function forModel(Model $model): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_type' => $model->getMorphClass(),
            'likeable_id' => $model->getKey(),
        ]);
    }
}
